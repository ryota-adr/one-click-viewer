<?php
$autoloader = '';

if (file_exists($autoloader)) {
    try {
        require($autoloader);
    } catch(Throwable $e) {
        $code = "invalid file path";
        goto output;
    } 
} else {
    $code = "invalid file path";
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

        preg_match('/^(class|interface|abstract class|trait|final class) (\w+)/m', $code, $match_class);
        if (isset($match_class[2])) {
            $this_class = $match_class[2];
        }

        preg_match('/^(class|interface|abstract class|trait|final class).+?extends (\w+)/m', $code, $match_parent);
        if (isset($match_parent[2])) {
            $parent_alias = $match_parent[2];
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
    $code = "invalid classname with namespace or file path";
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
preg_match('/^(class|interface|abstract class|trait|final class) \w+? (extends|implements) .+[\r\n]/m', $code, $match_ext_imp);

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
                    if ((new ReflectionClass($ext_imp_with_ns))->getFileName()) {
                        $replace_ext_imp_str = str_replace(" $ext_imp", " <a href=\"$url?ns=$ext_imp_with_ns\">$ext_imp</a>", $replace_ext_imp_str);

                        $class_arr[$ext_imp] = $ext_imp_with_ns;
                    }
                } catch (Exception $e) {}
            }
        }
        $code = str_replace($ext_imp_str, $replace_ext_imp_str, $code);
    }
}

//replace traits
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

                        $class_arr[$trait] = $trait_with_ns;
                    }
                } catch (Exception $e) {}
            }
        }
        $code = str_replace($traits_str, $replace_traits_str, $code);
    }
}

//replace docs
preg_match_all('/\/\*\*[\s\S]+?\*\//', $code, $match_docs_str);
if (isset($match_docs_str[0])) {
    $docs_str = $match_docs_str[0];
    foreach ($docs_str as $doc_str) {
        preg_match_all('/@\w+? +?([\w\\\]+)/', $doc_str, $match_classes_and_some);
        if (isset($match_classes_and_some[1])) {
            $classes_and_some = $match_classes_and_some[1];
            $classes_and_some = array_unique($classes_and_some);
            $replace_doc = $doc_str;
            foreach ($classes_and_some as $class_or_some) {
                if (isset($class_arr[$class_or_some])) {
                    $replace_doc = preg_replace(
                        '/'.$class_or_some.'( |\||,|[\r\n])/',
                        "<a href=\"$url?ns=$class_arr[$class_or_some]\">$class_or_some</a>$1",
                        $replace_doc
                    );
                } elseif (isset($alias_ns[$class_or_some])) {
                    $replace_doc = preg_replace(
                        '/'.$class_or_some.'( |\||,|[\r\n])/',
                        "<a href=\"$url?ns=$alias_ns[$class_or_some]\">$class_or_some</a>$1",
                        $replace_doc
                    );
                } else {
                    try {
                        if ((new ReflectionClass($class_or_some))->getFileName()) {
                            $replace_doc = str_replace($class_or_some, "<a href=\"$url?ns=$class_or_some\">$class_or_some</a>", $replace_doc);

                            $split = explode('\\', $class_or_some);
                            $end = end($split);
                            $class_arr[$end] = $class_or_some;
                        }
                    } catch (Exception $e) {
                        try {
                            $class_or_some_with_ns = $this_ns.'\\'.trim($class_or_some, '\\');
                            if ((new ReflectionClass($class_or_some_with_ns))->getFileName()) {
                                $replace_doc = str_replace($class_or_some, "<a href=\"$url?ns=$class_or_some_with_ns\">$class_or_some</a>", $replace_doc);

                                $class_arr[$class_or_some] = $class_or_some_with_ns;
                            }
                        } catch (Exception $e) {}
                    }
                }
            }
            $code = str_replace($doc_str, $replace_doc, $code);
        }
    }
}

//replace methods
preg_match_all('/(public|protected|private) (static )?function(.+?\);|[\s\S]+?^ {4}})/m', $code, $match_methods_str);

if (isset($match_methods_str[0])) {
    $methods_str = $match_methods_str[0];
    
    foreach ($methods_str as $method_str) {
        preg_match_all('/\\\?([A-Z][a-z]+\\\?)+/', $method_str, $match_method_classes);
        $method_classes = array_unique($match_method_classes[0]);
        $idx = array_search($this_class, $method_classes);
        if ($idx !== false) {
            array_splice($method_classes, $idx, 1);
        }

        $replace_method = $method_str;
        foreach ($method_classes as $method_class) {
            if (isset($class_arr[$method_class])) {
                $replace_method = preg_replace('/(?<![\w\\\])'.$method_class.'( |\(|\)|,|\]|;|::|[\r\n])/', "<a href=\"$url?ns=$class_arr[$method_class]\">$method_class</a>$1", $replace_method);
            } elseif (isset($alias_ns[$method_class])) {
                $replace_method = preg_replace('/(?<![\w\\\])'.$method_class.'( |\(|\)|,|\]|;|::|[\r\n])/', "<a href=\"$url?ns=$alias_ns[$method_class]\">$method_class</a>$1", $replace_method);
            } else {
                try {
                    $method_class_with_ns = $this_ns.'\\'.trim($method_class, '\\');
                    if ((new ReflectionClass($method_class_with_ns))->getFileName()) {
                        $replace_method = preg_replace('/(?<![\w\\\])'.preg_quote($method_class).'( |\(|\)|,|\]|;|::|[\r\n])/', "<a href=\"$url?ns=$method_class_with_ns\">$method_class</a>$1", $replace_method);
                    }
                } catch (Exception $e) {}
            }
        }
        $code = str_replace($method_str, $replace_method, $code);
    }
}
//replace properties
preg_match_all('/(public|protected|private) \$\w+?[\s\S]*?;/', $code, $match_properties_str);
$properties_str = $match_properties_str[0];
foreach ($properties_str as $property_str) {
    preg_match_all('/([\w\\\]+?)::class/', $property_str, $match_classes_with_ns);
    foreach ($match_classes_with_ns[1] as $class_with_ns) {
        try {
            if ((new ReflectionClass($class_with_ns))->getFileName()) {
                $code = str_replace(
                    "$class_with_ns::class",
                    "<a href=\"$url?ns=$class_with_ns\">$class_with_ns</a>::class",
                    $code
                );
            }
        } catch (Exception $e) {}
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
        '/(this\-'.htmlspecialchars('>').'|' . preg_quote($this_class) . '::|static::|self::)'.$func_name.'(\()/',
        "$1<a href=\"$url_with_query#$func_name\">$func_name</a>$2",
        $code
    );
}

//replace ($this->|Class::$)prop to <a href="thisUri#propName">($this->|Class::$)prop</a>
preg_match_all('/^ {4}(public|protected|private) (static )?(\$)(\w+?)( |;)/m', $code, $match_prop_names);
$prop_names = $match_prop_names[4];

foreach ($prop_names as $prop_name) {
    $code = preg_replace(
        '/(this\-'.htmlspecialchars('>').'|' . preg_quote($this_class) . '::\$|static::\$|self::\$)'.$prop_name.'(,|:|;|\)| |\.|\[|\-'.htmlspecialchars('>').')/',
        "$1<a href=\"$url_with_query#$prop_name\">$prop_name</a>$2",
        $code
    );
}

//replace const CONST_NAME to <a href="thisUri#CONST_NAME">CONST_NAME</a>
$code = preg_replace(
    '/(static|self|'.$this_class.')::(\w+)(,|:|\)| |\[)/',
    '$1::<a href="'.$url_with_query.'#$2">$2</a>$3',
    $code
);

//replace parant and parent funcs, props and consts
if (isset($parent_alias)) {
    if (isset($class_arr[$parent_alias])) {
        $parent_with_ns = $class_arr[$parent_alias];
    } elseif (isset($alias_ns[$parent_alias])) {
        $parent_with_ns = $alias_ns[$parent_alias];
    } else {
        try {
            new ReflectionClass($this_ns.'\\'.$parent_alias);
            $parent_with_ns = $this_ns.'\\'.$parent_alias;
        } catch (Exception $e) {}
    }
    if (isset($parent_with_ns)) {
        $code = preg_replace('/(parent)::/', "<a href=\"$url?ns=$parent_with_ns\">parent</a>::", $code);
        $code = preg_replace(
            '/parent<\/a>::(\$)?(\w+)/',
            "parent</a>::<a href=\"$url?ns=$parent_with_ns#$2\">$2</a>",
            $code
        );
    }
}

output:

$font_color = '#515151';
$font_size = '20px';
?>
<pre style="overflow-wrap: break-word; white-space: pre-wrap; font-family: Arial, Helvetica, sans-serif; font-size: $font_size; color: <?php echo $font_color ?>; margin-top: 2em;">
<div>
<?php
//print dir path
if (isset($autoloader) && isset($this_file)) {
    $dir = dirname($this_file);
    $dir_arr = explode('/', $autoloader);
    $idx = array_search('vendor', $dir_arr);
    $base_dir = $dir_arr[$idx - 1];
    $replative_dir = $base_dir.explode($base_dir, $dir, 2)[1];

    echo "<span class=\"dir\" data-dir=\"$dir\">$replative_dir</span>";
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
        font-size: <?php echo $font_size; ?>; 
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
        copyFrom.textContent = dir.dataset.dir;
        var bodyElm = document.getElementsByTagName("body")[0];
        bodyElm.appendChild(copyFrom);
        copyFrom.select();
        document.execCommand('copy');
        bodyElm.removeChild(copyFrom);

        window.open();
    }, false);
</script>