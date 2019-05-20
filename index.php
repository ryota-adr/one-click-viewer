<?php
$autoloader = '';

if (file_exists($autoloader)) {
    try {
        require($autoloader);
    } catch(Throwable $e) {
        $code = "invalid namespace";
        goto output;
    } 
} else {
    $code = "Invalid file path";
    goto output;
}

$url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER["HTTP_HOST"] . strtok($_SERVER["REQUEST_URI"],'?');

// get code from namespace
if (isset($_GET['ns'])) {
    $ns_or_path = $_GET['ns'];
    try {
        if (file_exists($ns_or_path)) { //file path
            $this_file = trim($ns_or_path);
            $code = file_get_contents($this_file);
        } else { //namespace
            $this_file = (new ReflectionClass(trim($ns_or_path)))->getFileName();
            $code = @file_get_contents($this_file);
        }

        preg_match('/^(class|interface|abstract class|trait) (\w+)/m', $code, $match_class);
        if (isset($match_class[2])) {
            $this_class = $match_class[2];
        }

        $pat_this_ns = '/namespace (.*?);/';
        preg_match('/namespace (.*?);/', $code, $match_this_ns);
        if (isset($match_this_ns[1])) {
            $this_ns = $match_this_ns[1];
        }
    } catch (Throwable $e) {
        $code = false;
    }
} else {
    $code = false;
}
?>
<div class="namespace">
    <form action="<?php basename(__FILE__) ?>" method="GET">
        <input type="text" name="ns" value="<?php if (isset($this_ns) && isset($this_class)) { echo $this_ns.'\\'.$this_class; } elseif (isset($ns_or_path)) { echo $ns_or_path; } ?>" size="60" class="ns">
        <button>SHOW</button>
    </form>
</div>
<?php
if ($code === false) {
    $code = "invalid namespace";
    goto output;
}

$code = htmlspecialchars($code);

//replace uses    
preg_match('/namespace[\s][A-Za-z\\\]+?;[\s\S]+?(class|interface|abstract class|trait)/', $code, $match_uses_str);

if (isset($match_uses_str[0])) {
    $uses_str = $match_uses_str[0];

    preg_match_all('/use ([a-z\\\]+?);/i', $uses_str, $match_uses);
    $classes_with_ns = $match_uses[1];
    $replace_uses_str = $uses_str;
    $class_arr = [];
    foreach ($classes_with_ns as $class_with_ns) {
        try {
            $path = (new ReflectionClass($class_with_ns))->getFileName();
            if ($path) {
                $split = explode('\\', $class_with_ns);
                $end = end($split);
                $class_arr[$end] = $class_with_ns;

                $replace_uses_str = preg_replace('/'.preg_quote($class_with_ns).';/', "<a href=\"$url? ns=$class_with_ns\">$class_with_ns</a>;", $replace_uses_str);
            }
        } catch (Exception $e) {}
    }
    $code = str_replace($uses_str, $replace_uses_str, $code);

    //replace uses with as
    preg_match_all('/use (([A-Za-z\\\]+?) as ([A-Za-z\\\]+?));/', $replace_uses_str, $match_use_as);

    $all_ns_alias_arr = [];
    for ($i = 0; $i < count($match_use_as[0]); $i++) {
        $all_ns_alias_arr[] = array_column($match_use_as, $i);
    }
    $all_ns_alias_arr = array_map(function($arr) {
        return [
            "all" => $arr[1],
            "ns" => $arr[2],
            "alias" => $arr[3]
        ];
    } ,$all_ns_alias_arr);

    $alias_ns = array_combine(
        array_column($all_ns_alias_arr, 'alias'), 
        array_column($all_ns_alias_arr, 'ns')
    );
    $replace_use_with_as_str = $replace_uses_str;

    foreach ($all_ns_alias_arr as $all_ns_alias) {
        try {
            $path = (new ReflectionClass($all_ns_alias['ns']))->getFileName();
            if ($path) {
                $replace_use_with_as_str = str_replace($all_ns_alias['all'], "<a href=\"$url?  ns=$all_ns_alias[ns]\">$all_ns_alias[all]</a>", $replace_use_with_as_str);
            }
        } catch (Exception $e) {}
    }
    $code = str_replace($replace_uses_str, $replace_use_with_as_str, $code);
}

//replace extends and implements
preg_match('/^(class|interface|abstract class|trait) \w+? (extends|implements) .+[\r\n]/m', $code, $match_ext_imp);

if (!empty($match_ext_imp[0])) {
    $ext_imp_str = $match_ext_imp[0];
    preg_match_all('/\\\?([A-Z][a-z]+\\\?)+/', $ext_imp_str, $match_exts_imps);
    if (!empty($exts_imps =  $match_exts_imps[0])) {    
        $idx = array_search($this_class, $exts_imps);
        if ($idx !== false) {
            array_splice($exts_imps, $idx, 1);
        }
        
        $replace_ext_imp_str = $ext_imp_str;
        foreach ($exts_imps as $ext_imp) {
            if (isset($class_arr[$ext_imp])) {
                $replace_ext_imp_str = preg_replace('/ '.$ext_imp.'( |,|[\r\n])/', " <a href=\"$url?ns=$class_arr[$ext_imp]\">$ext_imp</a>$1", $replace_ext_imp_str);
            } elseif (isset($alias_ns[$ext_imp])) {
                $replace_ext_imp_str = preg_replace('/ '.$ext_imp.'( |,|[\r\n])/', " <a href=\"$url?ns=$alias_ns[$ext_imp]\">$ext_imp</a>$1", $replace_ext_imp_str);
            } else {
                try {
                    $ext_imp_with_ns = $this_ns.'\\'.trim($ext_imp, '\\');
                    if ((new ReflectionClass($this_ns.'\\'.trim($ext_imp, '\\')))->getFileName()) {
                        $replace_ext_imp_str = str_replace(" $ext_imp", " <a href=\"$url?ns=$ext_imp_with_ns\">$ext_imp</a>", $replace_ext_imp_str);
                    }
                } catch (Exception $e) {}
            }
        }
        $code = str_replace($ext_imp_str, $replace_ext_imp_str, $code);
    }
}

//replace trait
preg_match('/ {4}use [\s\S]+? {4}(\/\*\*|public|protected|private)/', $code, $match_traits_str);

if (!empty($match_traits_str[0])) {
    $traits_str = $match_traits_str[0];
    preg_match_all('/\\\?([A-Z][a-z]+\\\?)+/', $traits_str, $match_traits);
    if (!empty($match_traits[0])) {
        $traits = array_unique($match_traits[0]);
        $replace_traits_str = $traits_str;
        foreach ($traits as $trait) {
            if (isset($class_arr[$trait])) {
                $replace_traits_str = preg_replace('/'.preg_quote($trait).'( |,|;|)/', "<a href=\"$url?ns=$class_arr[$trait]\">$trait</a>$1", $replace_traits_str);
            } elseif (isset($alias_ns[$trait])) {
                $replace_traits_str = preg_replace('/'.preg_quote($trait).'( |,|;|)/', "<a href=\"$url?ns=$alias_ns[$trait]\">$trait</a>$1", $replace_traits_str);
            } else {
                try {
                    $trait_with_ns = $this_ns.'\\'.trim($trait, '\\');
                    if ((new ReflectionClass($trait_with_ns))->getFileName()) {
                        $replace_traits_str = preg_replace('/'.preg_quote($trait).'( |,|;|)/', "<a href=\"$url?ns=$trait_with_ns\">$trait</a>$1", $replace_traits_str);
                    }
                } catch (Exception $e) {}
            }
        }
        $code = str_replace($traits_str, $replace_traits_str, $code);
    }
}

//replace doc
preg_match_all('/\/\*\*[\s\S]+?\*\//', $code, $match_docs_str);
if (isset($match_docs_str[0])) {
    $docs_str = $match_docs_str[0];
    foreach ($docs_str as $doc_str) {
        preg_match_all('/@\w+? +(\\\?([A-Z][a-z]+\\\?)+)/', $doc_str, $match_classes_with_ns);
        if (isset($match_classes_with_ns[1])) {
            $classes_with_ns = $match_classes_with_ns[1];
            $classes_with_ns = array_unique($classes_with_ns);
            $replace_doc = $doc_str;
            foreach ($classes_with_ns as $class_with_ns) {
                try {
                    if ((new ReflectionClass($class_with_ns))->getFileName()) {
                        $replace_doc = str_replace($class_with_ns, "<a href=\"$url?ns=$class_with_ns\">$class_with_ns</a>", $replace_doc);

                        $split = explode('\\', $class_with_ns);
                        $end = end($split);
                        $class_arr[$end] = $class_with_ns;
                    }
                } catch (Exception $e) {}
            }
            $code = str_replace($doc_str, $replace_doc, $code);
        }
    }
}

//replace function
preg_match_all('/function[\s\S]+?^ {4}}/m', $code, $match_funcs_str);

if (isset($match_funcs_str[0])) {
    $funcs_str = $match_funcs_str[0];
    
    foreach ($funcs_str as $func_str) {
        preg_match_all('/\\\?([A-Z][a-z]+\\\?)+/', $func_str, $match_func_classes);
        $func_classes = array_unique($match_func_classes[0]);
        $idx = array_search($this_class, $func_classes);
        if ($idx !== false) {
            array_splice($func_classes, $idx, 1);
        }

        $replace_func = $func_str;
        foreach ($func_classes as $func_class) {
            if (isset($class_arr[$func_class])) {
                $replace_func = preg_replace('/(?<![\w\\\])'.$func_class.'( |\(|\)|;|::)/', "<a href=\"$url?ns=$class_arr[$func_class]\">$func_class</a>$1", $replace_func);
            } elseif (isset($alias_ns[$func_class])) {
                $replace_func = preg_replace('/(?<![\w\\\])'.$func_class.'( |\(|\)|;|::)/', "<a href=\"$url?ns=$alias_ns[$func_class]\">$func_class</a>$1", $replace_func);
            } else {
                try {
                    $func_class_with_ns = $this_ns.'\\'.trim($func_class, '\\');
                    if ((new ReflectionClass($func_class_with_ns))->getFileName()) {
                        $replace_func = preg_replace('/(?<![\w\\\])'.preg_quote($func_class).'( |\(|\)|;|::)/', "<a href=\"$url?ns=$func_class_with_ns\">$func_class</a>$1", $replace_func);
                    }
                } catch (Exception $e) {}
            }
        }
        $code = str_replace($func_str, $replace_func, $code);
    }
}

//add <span id="funcName or propName"> </span> 
//and replace ($this->|Class::)func() to <a href="thisUri#funcName>($this->|Class::)func()</a>
$code = preg_replace( //with doc
    '/([\r\n])(^ {4}\/\*\*)([\s\S]+?^ {5}?\*\/[\r\n]+^ {4})(abstract )?(const|public|protected|private) (static )?(function )?(\$)?(\w+)/m',
    '<span id="$9"> </span>$1$2$3$4$5 $6$7$8$9',
    $code
);
$code = preg_replace( //without doc
    '/(?<!\*\/)([\r\n])(^ {4})(abstract )?(const|public|protected|private) (static )?(function )?(\$)?(\w+)/m',
    '<span id="$8"> </span>$1$2$3$4 $5$6$7$8',
    $code
);

$url_with_query =  (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];

preg_match_all('/^ {4}(abstract )?(public|protected|private)(.+?)(function )(\w+)(\()/m', $code, $match_func_names);
$func_names = $match_func_names[5];

foreach ($func_names as $func_name) {
    $code = preg_replace(
        '/(this\-'.htmlspecialchars('>').'|' . preg_quote($this_class) . '::)'.$func_name.'(\()/',
        "$1<a href=\"$url_with_query#$func_name\">$func_name</a>$2",
        $code
    );
}

//replace ($this->|Class::$)prop to <a href="thisUri#propName">($this->|Class::$)prop</a>
preg_match_all('/^ {4}(public|protected|private) (static )?(\$)(\w+?)( |;)/m', $code, $match_prop_names);
$prop_names = $match_prop_names[4];

foreach ($prop_names as $prop_name) {
    $code = preg_replace(
        '/(this\-'.htmlspecialchars('>').'|' . preg_quote($this_class) . '::\$|static::\$|self::\$)'.$prop_name.'(,|:|\)| =|\[)/',
        "$1<a href=\"$url_with_query#$prop_name\">$prop_name</a>",
        $code
    );
}

//replace const CONST_NAME to <a href="thisUri#CONST_NAME">CONST_NAME</a>
$code = preg_replace(
    '/(static|self|'.$this_class.')::(\w+)(,|:|\)| =|\[)/',
    '$1::<a href="'.$url_with_query.'#$2">$2</a>$3',
    $code
);

output:

$font_color = '#515151';
$font_size = '20px';
?>
<pre style="overflow-wrap: break-word; white-space: pre-wrap; font-family: Arial, Helvetica, sans-serif; font-size: $font_size; color: <?php echo $font_color ?>; margin-top: 2em;">
<div>
<?php
//print dir path
if (isset($this_file)) {
    $dir = dirname($this_file);
    echo "<span class=\"dir\">$dir</span>";
}
?>
</div>
<div>
<table>
<?php
//print code with row num
$lines = explode("\n", $code);
$count = count($lines);
$i = 1;
foreach ($lines as $line) {
    echo '<tr><td><pre><span class="num">'.($i++).'<span></pre></td><td><pre>'.$line.'</pre></td></tr>';
}
$div_width = (strlen($count) + 1) * 9; 
?>
</table>
</div>
</pre>
<style>
    div.namespace {
        position: fixed; top: 0px; left: 0px;
    }
    input.ns {
        font-size: 1.2em;
        margin-right: -6px;
        padding-left: 5px;
    }
    button {
        font-size: 1.2em;
    }
    table {
            border-spacing: 0px;
    }
    td {
        vertical-align:top;
    }
    .num {
        display: block;
        text-align: center;
        margin-right: 5px;
        user-select: none;
        width: <?php echo $div_width; ?>px;
    }
    pre {
        color: <?php echo $font_color ?>;
        font-family: Arial, Helvetica, sans-serif; 
        font-size: 20px; 
        margin: 0;
        overflow-wrap: break-word; 
        white-space: pre-wrap; 
    }
    a {
        color: <?php echo $font_color ?>;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
    span.dir:hover {
        background-color: #e2e6ff;
        color: #191919;
        cursor: pointer;
    }
    
</style>
<!-- copy dir path -->
<script>
    var dir = document.querySelector("span.dir");
    dir.addEventListener("click", function() {
        var copyFrom = document.createElement("textarea");
        copyFrom.textContent = dir.textContent;
        var bodyElm = document.getElementsByTagName("body")[0];
        bodyElm.appendChild(copyFrom);
        copyFrom.select();
        document.execCommand('copy');
        bodyElm.removeChild(copyFrom);
    }, false);
</script>