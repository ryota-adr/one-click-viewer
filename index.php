<?php
$autoloader = 'file:///C:/xampp/htdocs/TestPHP/myapp/laravel58/vendor/autoload.php';

if (file_exists($autoloader)) {
    try {
        require($autoloader);
    } catch(Throwable $e) {
        $code = "Invalid file path.";
        goto output;
    } 
} else {
    $code = "Invalid file path.";
    goto output;
}

$url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER["HTTP_HOST"] . strtok($_SERVER["REQUEST_URI"],'?');

// get code from namespace
if (isset($_GET['ns'])) {
    $namespace = $_GET['ns'];
    try {
        $this_file = (new ReflectionClass($namespace))->getFileName();
        $code = @file_get_contents($this_file);

        preg_match('/^(class|interface|abstruct class) (\w+) /m', $code, $match_class);
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
    <form action="index.php" method="GET">
        <input type="text" name="ns" value="<?php if (isset($namespace)) echo $namespace ?>" size="60" class="ns">
        <button>SHOW</button>
    </form>
</div>
<?php
if ($code === false) {
    $code = "This namespace is invalid.";
    goto output;
}

$code = htmlspecialchars($code);

//replace uses    
preg_match('/namespace[\s][A-Za-z\\\]+?;[\s\S]+?(class|interface|abstruct class)/', $code, $match_uses);
if (empty($match_uses)) {
    goto output;
}
$uses = $match_uses[0];

preg_match_all('/use ([a-z\\\]+?);/i', $uses, $match_use);
$replace_use = $uses;
$class_arr = [];
foreach ($match_use[1] as $class_with_ns) {
    try {
        $path = (new ReflectionClass($class_with_ns))->getFileName();
        if ($path) {
            $split = explode('\\', $class_with_ns);
            $end = end($split);
            $class_arr[$end] = $class_with_ns;

            $replace_use = preg_replace('/'.preg_quote($class_with_ns).';/', "<a href=\"$url?ns=$class_with_ns\">$class_with_ns</a>;", $replace_use);
        }
    } catch (Exception $e) {

    }
}
$code = str_replace($uses, $replace_use, $code);

//replace uses with as
$pat_uses_as = '/use ([A-Za-z\\\]+?) as ([A-Za-z\\\]+?);/';
preg_match_all('/use (([A-Za-z\\\]+?) as ([A-Za-z\\\]+?));/', $replace_use, $match_use_as);

$all_ns_alias = [];
for ($i = 0; $i < count($match_use_as[0]); $i++) {
    $all_ns_alias[] = array_column($match_use_as, $i);
}
$all_ns_alias = array_map(function($arr) {
    return [
        "all" => $arr[1],
        "ns" => $arr[2],
        "alias" => $arr[3]
    ];
} ,$all_ns_alias);

$alias_ns = array_combine(
    array_column($all_ns_alias, 'alias'), 
    array_column($all_ns_alias, 'ns')
);
$replace_use_with_as = $replace_use;

foreach ($all_ns_alias as $arr) {
    try {
        $path = (new ReflectionClass($arr['ns']))->getFileName();
        if ($path) {
            $replace_use_with_as = str_replace($arr['all'], "<a href=\"$url?ns=$arr[ns]\">$arr[all]</a>", $replace_use_with_as);
        }
    } catch (Exception $e) {}
}
$code = str_replace($replace_use, $replace_use_with_as, $code);

//replace extends and implements
preg_match('/^(class|interface|abstract class) \w+? (extends|implements) .+[\r\n]/m', $code, $match_ext_imp);
if (!empty($match_ext_imp[0])) {
    $ext_imp_str = $match_ext_imp[0];
    preg_match_all('/[\w\\\]+/', $ext_imp_str, $match_each_ext_imp);
    if (!empty($each_ext_imp =  $match_each_ext_imp[0])) {
        foreach (['class', 'interface', 'abstruct class', 'extends', $this_class] as $remove) {
            $idx = array_search($remove, $each_ext_imp);
            
            if ($idx !== false) {
                array_splice($each_ext_imp, $idx, 1);
            }
        }

        $replace_ext_imp_str = $ext_imp_str;
        foreach ($each_ext_imp as $ext_imp) {
            if (isset($class_arr[$ext_imp])) {
                $replace_ext_imp_str = preg_replace('/ '.$ext_imp.'( |,|[\r\n])/', " <a href=\"$url?ns=$class_arr[$ext_imp]\">$ext_imp</a>$1", $replace_ext_imp_str);
            } elseif (isset($alias_ns[$ext_imp])) {
                $replace_ext_imp_str = preg_replace('/ '.$ext_imp.'( |,|[\r\n])/', " <a href=\"$url?ns=$alias_ns[$ext_imp]\">$ext_imp</a>$1", $replace_ext_imp_str);
            } else {
                try {
                    $ext_imp_with_ns = $this_ns.'\\'.trim($ext_imp, '\\');
                    if ((new ReflectionClass($this_ns.'\\'.trim($ext_imp, '\\')))->getFileName()) {
                        $replace_ext_imp_str = str_replace($ext_imp, "<a href=\"$url?ns=$ext_imp_with_ns\">$ext_imp</a>", $replace_ext_imp_str);
                    }
                } catch (Exception $e) {}
            }
        }
        $code = str_replace($ext_imp_str, $replace_ext_imp_str, $code);
    }
}

//replace trait
preg_match_all('/^ {4}use .+?[\r\n]+/m', $code, $match_traits);

if (!empty($match_traits[0])) {
    $traits_str_arr = $match_traits[0];
    foreach ($traits_str_arr as $trait_str) {  
        preg_match_all('/[\w\\\]+/', $trait_str, $match_each_trait);

        if (!empty($each_trait_arr = $match_each_trait[0])) {
            $replace_trait_str = $trait_str;
            foreach ($each_trait_arr as $trait) {
                if (isset($class_arr[$trait])) {
                    $replace_trait_str = str_replace($trait, "<a href=\"$url?ns=$class_arr[$trait]\">$trait</a>", $replace_trait_str);
                } elseif (isset($alias_ns[$trait])) {
                    $replace_trait_str = str_replace($trait, "<a href=\"$url?ns=$alias_ns[$trait]\">$trait</a>", $replace_trait_str);
                } else {
                    try {
                        $trait_with_ns = $this_ns.'\\'.trim($trait, '\\');
                        $refletion_trait = (new ReflectionClass($trait_with_ns))->getFileName();
                        if ((new ReflectionClass($trait_with_ns))->getFileName()) {
                            $replace_trait_str = str_replace($trait, "<a href=\"$url?ns=$trait_with_ns\">$trait</a>", $replace_trait_str);

                            $split = explode('\\', $trait_with_ns);
                            $end = end($split);
                            $class_arr[$end] = $trait_with_ns;
                        }
                    } catch (Exception $e) {}
                }
            }
            $code = str_replace($trait_str, $replace_trait_str, $code);
        }
    }
}

//replace doc
preg_match_all('/\/\*\*[\s\S]+?\*\//', $code, $match_doc);
if (isset($match_doc[0])) {
    $docs = $match_doc[0];
    foreach ($docs as $doc) {
        preg_match_all('/@\w+? +(\\\?([A-Z][a-z]+\\\?)+)/', $doc, $match_classes_with_ns);
        if (isset($match_classes_with_ns[1])) {
            $classes_with_ns = $match_classes_with_ns[1];
            
            $replace_doc = $doc;
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
            $code = str_replace($doc, $replace_doc, $code);
        }
    }
}

//replace function
$pat_func = '/function[\s\S]+?^ {4}}/m';
preg_match_all($pat_func, $code, $match_funcs);

if (isset($match_funcs[0])) {
    $funcs = $match_funcs[0];

    foreach ($funcs as $func) {
        preg_match_all('/\\\?([A-Z][a-z]+\\\?)+/', $func, $match_class);
        $func_classes = array_unique($match_class[0]);

        $replace_func = $func;
        foreach ($func_classes as $func_class) {
            if (isset($class_arr[$func_class])) {
                $replace_func = preg_replace('/(?<!\w\\\)'.$func_class.'( |\(|;|::)/', "<a href=\"$url?ns=$class_arr[$func_class]\">$func_class</a>$1", $replace_func);
            } elseif (isset($alias_ns[$func_class])) {
                $replace_func = preg_replace('/(?<!\w\\\)'.$func_class.'( |\(|;|::)/', "<a href=\"$url?ns=$alias_ns[$func_class]\">$func_class</a>$1", $replace_func);
            } else {
                try {
                    $func_class_with_ns = $this_ns.'\\'.trim($func_class, '\\');
                    if ((new ReflectionClass($func_class_with_ns))->getFileName()) {
                        $replace_func = preg_replace('/'.preg_quote($func_class).'( |\(|;|::)/', "<a href=\"$url?ns=$func_class_with_ns\">$func_class</a>$1", $replace_func);
                    }
                } catch (Exception $e) {}
            }
        }
        $code = str_replace($func, $replace_func, $code);
    }
}


output:

$font_color = "#515151";
?>
<pre style="overflow-wrap: break-word; white-space: pre-wrap; font-family: Arial, Helvetica, sans-serif; font-size: 20px; color: <?php echo $font_color ?>;">
<?php
if (isset($this_file)) {
    $dir = dirname($this_file);
    echo "<span class=\"dir\">$dir</span><br><br>";
}
echo $code;
?>
</pre>
<style>
    input.ns {
        font-size: 1.2em;
    }
    button {
        font-size: 1.2em;
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