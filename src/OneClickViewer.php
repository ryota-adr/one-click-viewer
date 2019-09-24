<?php
class OneClickViewer
{
    /**
     * The paths of autoloader.
     *
     * @var string[]
     */
    protected $autoloaders = [];

    /**
     * @var bool[]
     */
    protected $wasRequired = [];

    /**
     * @var string[]
     */
    protected $requiredAutoloaders = [];

    /**
     * The path of style sheet.
     *
     * @var string
     */
    protected $cssPath = "src/css/style.css";

    protected $fontsPath = "src/css/icomoon/style.css";

    /**
     * Html head tag.
     *
     * @var string
     */
    protected $head = "";

    /**
     * Fully qualified class name or path from query string.
     *
     * @var string
     */
    protected $classNameOrPath = "";

    /**
     * Url of this viewer with query string.
     *
     * @var string
     */
    protected $urlWithQuery = "";

    /**
     * Url of this viewr without querys string.
     *
     * @var string
     */
    protected $urlWithoutQuery = "";

    /**
     * Current php file path.
     *
     * @var string
     */
    protected $currentFilePath = "";

    /**
     * Current php code.
     *
     * @var string
     */
    protected $code = "";

    /**
     * Current class name.
     *
     * @var string
     */
    protected $currentClass = "";

    /**
     * Parent class of current class.
     *
     * @var string
     */
    protected $parentClass = "";

    /**
     * Current namespace.
     *
     * @var string
     */
    protected $namespace = "";

    /**
     * The array of ancestors and traits.
     *
     * @var \ReflectionClass[]
     */
    protected $ancestorsAndTraits = [];

    /**
     * The array has class name as key and fully qualified name as value.
     *
     * @var array
     */
    protected $classArr = [];

    /**
     * The array of declared properties.
     *
     * @var string[]
     */
    protected $declaredProps = [];

    /**
     * The array of declared methods.
     *
     * @var string[]
     */
    protected $declaredMethods = [];

    /**
     * The array of declared constants.
     *
     * @var string[]
     */
    protected $declaredConsts = [];

    /**
     * The directory of current php file.
     *
     * @var string
     */
    protected $dirUri = "";

    /**
     * Html string of file link list.
     *
     * @var string
     */
    protected $fileList = "";

    /**
     * Html form tag.
     *
     * @var string
     */
    protected $form = "";

    /**
     * Html for output.
     *
     * @var string
     */
    protected $html = "";

    /**
     * if an error occured, true.
     *
     * @var bool
     */
    protected $occuredError = false;

    /**
     * Error message.
     *
     * @var string
     */
    protected $message = "";

    /**
     * Javascript path.
     *
     * @var string
     */
    protected $jsPath = "src/js/script.js";

    /**
     * Creat a new One Click Viewer instance.
     *
     * @param string $envPath
     * @return void
     */
    public function __construct($envPath = '.env')
    {
        $this->setAutoloaderPaths($envPath);

        $this->urlWithQuery = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];
        $this->urlWithoutQuery = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER["HTTP_HOST"] . strtok($_SERVER["REQUEST_URI"], '?');
        $this->dirUrl = dirname($this->urlWithoutQuery . "/");

        $this->setHead();
        $this->requireAutoloader($this->autoloaders);

        if (isset($_GET['q'])) {
            $this->classNameOrPath = $_GET['q'];
            $this->setCode();
            $this->setCurrentClass();
            $this->setNamespace();
            $this->setForm();
        } else {
            $this->occuredError = true;
            $this->setForm();
            $this->message = "Invalid class name or path.";
        }
    }

    /**
     * Set path of autoloader.php.
     *
     * @param string $envPath
     * return void
     */
    protected function setAutoloaderPaths($envPath)
    {
        $path = file_get_contents($envPath);
        preg_match('/AUTOLOADERPATH=.+/', $path, $match);

        $this->autoloaders = $match ? explode(',', str_replace('\\', '/', str_replace('AUTOLOADERPATH=', '', $path))) : [];
    }

    /**
     * Make html head tag.
     *
     * return void
     */
    protected function setHead()
    {
        $head = <<<HEAD
<head>
    <meta charset="utf-8">
    <title>One Click Viewer</title>
    <link rel="stylesheet" href="$this->cssPath">
    <link rel="stylesheet" href="$this->fontsPath">
</head>
HEAD;
        $this->head = $head;
    }

    /**
     * Require autoloader.php.
     *
     * @param string[] $autoloaders
     * return void
     */
    protected function requireAutoloader($autoloaders)
    {
        foreach ($autoloaders as $autoloader) {
            if (file_exists($autoloader)) {
                try {
                    require $autoloader;
                    $this->wasRequired[] = true;
                    $this->requiredAutoloaders[] = $autoloader;
                } catch (Exception $e) {
                    $this->wasRequired[] = false;
                }
            }
        }
        
        foreach ($this->wasRequired as $wasRequired) {
            if ($wasRequired === false) {
                $this->message = "invalid file path.";
                $this->occuredError = true;
                break;
            }
        }
    }

    /**
     * Make html form tag.
     *
     * return boid
     */
    protected function setForm()
    {
        $phpSelf = basename($_SERVER['PHP_SELF']);
        $value = (!empty($this->namespace) and !empty($this->currentClass)) ? $this->namespace . '\\' . $this->currentClass : '';
        $form = <<<FORM
<form action="$this->urlWithoutQuery" method="GET">
    <div class="inline-block" role="input-text">
        <input type="text" name="q" value="$value" size="60" class="ns">
        <button>SHOW</button>
    </div>
    <div class="inline-block" role="toggle-input-text">
    </div>
</form>
FORM;
        $this->form = $form;
    }

    /**
     * make html to output.
     *
     * return $this
     */
    public function html()
    {
        if (!$this->occuredError) {
            //$this->setDirUri();
            $this->setParentClass();
            $this->setAncestorsAndTraits();
            $this->setDeclaredProps();
            $this->setDeclaredMethods();
            $this->setDeclaredConsts();

            $this->replaceClassesInUse()
                ->replaceOtherClasses()
                ->addSpanIds()
                ->replaceCalledProps()
                ->replaceCalledMethods()
                ->replaceCalledConsts()
                ->replaceExtendedProps()
                ->replaceExtendedMethods()
                ->replaceExtendedConsts()
                ->replaceParentPropsMethodsConsts()
                ->replaceInternalFunctions();

            $this->code2Table();
            $this->setFileList();

            $jsLink = $this->jsLink();

            $this->html = <<<BODY
$this->head
<body>
    <div class="container">
        <div class="namespace">
            $this->form
        </div>
        <div class="dir">
            <pre>$this->dirUri</pre>
        </div>
        <div>
            $this->fileList
        </div>
        <div>
            $this->code
        </div>
        <div>
            $jsLink
        </div>
    </div>
</body>
BODY;
        } else {
            $head = $this->head;
            $message = $this->message;
            $form = $this->form;
            $this->html = <<<BODY
$this->head
<body>
    <div class="container">
        <div class="namespace">
            $this->form
        </div>
        <div class="message">
           <pre>$this->message</pre>
        </div>
    </div>
</body>
BODY;
        }
        $this->html = <<<HTML
<html>
    $this->html
</html>
HTML;
        return $this;
    }

    /**
     * Set a php code.
     *
     * return void
     */
    protected function setCode()
    {
        if (isset($this->classNameOrPath)) {
            try {
                if (file_exists($this->classNameOrPath)) { //file path
                    $this->currentFilePath = $this->classNameOrPath;
                    $this->code = htmlspecialchars(file_get_contents($this->currentFilePath));
                } else { //fully qualified name
                    $this->currentFilePath = (new ReflectionClass($this->classNameOrPath))->getFileName();
                    $this->code = htmlspecialchars(@file_get_contents($this->currentFilePath));
                }
            } catch (Exception $e) {
                $this->occuredError = true;
                $this->message = "Invalid class name or path.";
            }
        }
    }

    /**
     * Set directory URI wrapped html span.
     *
     * return void
     */
    protected function setDirUri()
    {
        $dir = dirname($this->currentFilePath);
        var_dump($dir);

        foreach ($this->requiredAutoloaders as $requiredAutoloader) {
            $dirArr = explode('/', $requiredAutoloader);
            $baseDir = $dirArr[array_search('vendor', $dirArr) - 1];
            var_dump($baseDir);
            $replativeDir = $baseDir . explode($baseDir, $dir, 2)[1];

            $this->dirUri .= "<div class=\"dir\" data-dir=\"$dir\">$replativeDir</div>";
        }
    }

    /**
     * Set current class name.
     *
     * return void
     */
    protected function setCurrentClass()
    {
        preg_match('/^(class|interface|abstract class|trait|final class) (\w+)/m', $this->code, $match_class);
        if (isset($match_class[2])) {
            $this->currentClass = $match_class[2];
        }
    }

    /**
     * Set current namespace.
     *
     * return void
     */
    protected function setNamespace()
    {
        preg_match('/namespace (.*?);/', $this->code, $match_this_ns);
        if (isset($match_this_ns[1])) {
            $this->namespace = $match_this_ns[1];
        }
    }

    /**
     * Set parent class name of current class.
     *
     * return void
     */
    protected function setParentClass()
    {
        preg_match('/^(class|interface|abstract class|trait|final class).+?extends (\w+)/m', $this->code, $match_parent);
        if (isset($match_parent[2])) {
            $this->parentClass = $match_parent[2];
        }
    }

    /**
     * Set ancestors and traits.
     *
     * return void
     */
    protected function setAncestorsAndTraits()
    {
        $reflectionThisClass = new ReflectionCLass($this->namespace . '\\' . $this->currentClass);
        $this->ancestorsAndTraits = array_values($reflectionThisClass->getTraits());
        $reflectionParent = $reflectionThisClass->getParentClass();

        while ($reflectionParent) {
            foreach ($reflectionParent->getTraits() as $trait) {
                if (strpos($trait->getName(), '\\')) {
                    $this->ancestorsAndTraits[] = $trait;
                }
            }

            if (strpos($reflectionParent->getName(), '\\')) {
                $this->ancestorsAndTraits[] = $reflectionParent;
            }
            $reflectionParent = $reflectionParent->getParentClass();
        }
        $this->ancestorsAndTraits = array_reverse($this->ancestorsAndTraits);
    }

    /**
     * Set declared peroperties.
     *
     * return void
     */
    protected function setDeclaredProps()
    {
        preg_match_all(
            '/(public|protected|private) (static )?\$(\w+)/',
            $this->code,
            $matchDeclaredProps
        );
        $this->declaredProps = $matchDeclaredProps[3];
    }

    /**
     * Set declared methods.
     *
     * return void
     */
    protected function setDeclaredMethods()
    {
        preg_match_all(
            '/(public|protected|private) (static )?function (\w+)/',
            $this->code,
            $matchDeclaredMethods
        );
        $this->declaredMethods = $matchDeclaredMethods[3];
    }

    /**
     * Set declared constants.
     *
     * return void
     */
    protected function setDeclaredConsts()
    {
        preg_match_all(
            '/const (static )?(\w+)/',
            $this->code,
            $matchDeclaredConsts
        );
        $this->declaredConsts = $matchDeclaredConsts[2];
    }
    /**
     * Replace use statements.
     * use Foo\Bar\Class( as Alias) -> use <a href="URL?q=Foo\Bar\Class">Foo\Bar\Class( as Alias)</a>
     *
     * return $this
     */
    protected function replaceClassesInUse()
    {
        preg_match('/namespace[\s][A-Za-z\\\]+?;[\s\S]+?(class|interface|abstract class|trait)/', $this->code, $matchUsesStr);

        if (isset($matchUsesStr[0])) {
            $usesStr = $matchUsesStr[0];

            //without as
            preg_match_all('/use ([\w\\\]+?);/', $usesStr, $match_uses);
            $classes_with_ns = $match_uses[1];
            $replace_uses_str = $usesStr;

            foreach ($classes_with_ns as $class_with_ns) {
                try {
                    $path = (new ReflectionClass($class_with_ns))->getFileName();
                    if ($path) {
                        $split = explode('\\', $class_with_ns);
                        $end = end($split);
                        $this->classArr[$end] = $class_with_ns;

                        $replace_uses_str = preg_replace(
                            '/' . preg_quote($class_with_ns) . ';/',
                            "<a href=\"$this->urlWithoutQuery?q=$class_with_ns\" role=\"link\">$class_with_ns</a>;", $replace_uses_str
                        );
                    }
                } catch (Exception $e) {}
            }

            //with as
            preg_match_all('/use ([A-Za-z\\\]+?) as ([A-Za-z\\\]+?);/', $replace_uses_str, $match_use_as);

            for ($i = 0; $i < count($match_use_as[0]); $i++) {
                $class_with_ns = $match_use_as[1][$i];
                $alias = $match_use_as[2][$i];
                $this->classArr[$alias] = $class_with_ns;

                try {
                    if ((new ReflectionClass($class_with_ns))->getFileName()) {

                        $replace_uses_str = str_replace(
                            "$class_with_ns as $alias",
                            "<a href=\"$this->urlWithoutQuery?q=$class_with_ns\" role=\"link\">$class_with_ns as $alias</a>",
                            $replace_uses_str
                        );
                    }
                } catch (Exception $e) {}
            }
            $this->code = str_replace($usesStr, $replace_uses_str, $this->code);
        }
        return $this;
    }

    /**
     * Replace all classes except in use statements.
     *
     * return $this
     */
    protected function replaceOtherClasses()
    {
        preg_match(
            '/^(class|abstract class|final class|interface|trait) ' . $this->currentClass . '[\s\S]+/m',
            $this->code,
            $matchExceptUses
        );
        $matchExceptUses = $matchExceptUses[0];

        preg_match_all(
            '/((\\\?[A-Z][a-z]+)+)(;|:|\r|\n| |,|\(|\)|\[|\]|\|)/',
            $this->code,
            $matchClasses
        );

        $classes = array_unique($matchClasses[1]);
        $idx = array_search($this->currentClass, $classes);
        array_splice($classes, $idx, 1);

        $replaceExceptUses = $matchExceptUses;

        foreach ($classes as $class) {
            if (isset($this->classArr[$class])) {
                $fullyQualifiedName = preg_quote($this->classArr[$class]);

                $replaceExceptUses = preg_replace(
                    '/(\\\)?' . $fullyQualifiedName . '(;|:|\r|\n| |,|\(|\)|\[|\]|\|)/',
                    "<a href=\"$this->urlWithoutQuery?q=$1$fullyQualifiedName\" role=\"link\">$fullyQualifiedName</a>$2",
                    $replaceExceptUses
                );

                $replaceExceptUses = preg_replace(
                    '/(?<![\\\a-zA-Z])' . $class . '(;|:|\r|\n| |,|\(|\)|\[|\]|\|)/',
                    "<a href=\"$this->urlWithoutQuery?q=$fullyQualifiedName\" role=\"link\">$class</a>$1",
                    $replaceExceptUses
                );
            } else {
                try {
                    //same namespace classes
                    $class = trim($class, '\\');
                    $fullyQualifiedName = $this->namespace . '\\' . $class;

                    if ((new ReflectionClass($fullyQualifiedName))->getFileName()) {
                        $fullyQualifiedName = preg_quote($fullyQualifiedName);

                        $replaceExceptUses = preg_replace(
                            '/(?<![\\\a-zA-Z])' . preg_quote($class) . '(;|:|\r|\n| |,|\(|\)|\[|\]|\|)/',
                            "<a href=\"$this->urlWithoutQuery?q=$fullyQualifiedName\" role=\"link\">$class</a>$1",
                            $replaceExceptUses
                        );
                    }
                } catch (Exception $e) {
                    try {
                        //fully qualified names
                        if ((new ReflectionClass($class))->getFileName()) {
                            $class = preg_quote($class);

                            $replaceExceptUses = preg_replace(
                                '/' . $class . '(:|:|\r|\n| |,|\(|\)|\[|\]|\|)/',
                                "<a href=\"$this->urlWithoutQuery?q=$class\" role=\"link\">$class</a>$1",
                                $replaceExceptUses
                            );
                        }
                    } catch (Exception $e) {}
                }
            }
        }
        $this->code = str_replace($matchExceptUses, $replaceExceptUses, $this->code);
        return $this;
    }

    /**
     * Replace declared methods.
     *
     * return $this
     */
    protected function replaceDeclaredMethods()
    {
        preg_match_all('/(public|protected|private) (static )?function(.+?\);|[\s\S]+?^ {4}})/m', $this->code, $matchMethodsStr);

        if (isset($matchMethodsStr[0])) {
            $methodsStr = $matchMethodsStr[0];

            foreach ($methodsStr as $methodStr) {
                preg_match_all('/\\\?([A-Z][a-z]+\\\?)+/', $methodStr, $matchMethodClasses);
                $methodClasses = array_unique($matchMethodClasses[0]);
                $idx = array_search($this->currentClass, $methodClasses);
                if ($idx !== false) {
                    array_splice($methodClasses, $idx, 1);
                }

                $replaceMethod = $methodStr;
                foreach ($methodClasses as $methodClass) {
                    if (isset($this->classArr[$methodClass])) {
                        $class = $this->classArr[$methodClass];
                        $replaceMethod = preg_replace(
                            '/(?<![\w\\\])' . $methodClass . '( |\(|\)|,|\]|;|::|[\r\n])/',
                            "<a href=\"$this->urlWithoutQuery?q=$class\" role=\"link\">$methodClass</a>$1",
                            $replaceMethod
                        );
                    } else {
                        try {
                            $methodClassWithNamespace = $this->namespace . '\\' . trim($methodClass, '\\');
                            if ((new ReflectionClass($methodClassWithNamespace))->getFileName()) {
                                $replaceMethod = preg_replace(
                                    '/(?<![\w\\\])' . preg_quote($methodClass) . '( |\(|\)|,|\]|;|::|[\r\n])/',
                                    "<a href=\"$this->urlWithoutQuery?q=$methodClassWithNamespace\" role=\"link\">$methodClass</a>$1",
                                    $replaceMethod);

                                $this->classArr[trim($methodClass, '\\')] = $methodClassWithNamespace;
                            }
                        } catch (Exception $e) {}
                    }
                }
                $this->code = str_replace($methodStr, $replaceMethod, $this->code);
            }
        }
        return $this;
    }

    /**
     * Add <span id="delaredName"> </span> above documentations or declarations.
     *
     * return $this
     */
    protected function addSpanIds()
    {
        //with doc
        $this->code = preg_replace(
            '/([\r\n])(^ {4}\/\*\*)([\s\S]+?^ {5}?\*\/[\r\n]+^ {4})(abstract )?(const|public|protected|private) (static )?(function )?(\$)?(\w+)/m',
            '<span id="$9"> </span>$1$2$3$4$5 $6$7$8<span class="$9">$9</span>',
            $this->code
        );

        //without doc
        $this->code = preg_replace(
            '/(?<!\*\/)([\r\n])(^ {4})(abstract )?(const|public|protected|private) (static )?(function )?(\$)?(\w+)/m',
            '<span id="$8"> </span>$1$2$3$4 $5$6$7<span class="$8">$8</span>',
            $this->code
        );
        return $this;
    }

    /**
     * Replace called properties.
     * $this->prop, Class::$prop, static::$prop and self::$prop ->
     * ($this->|Class::$|static::$|self::$)<a href="URL#prop" role="link">prop</a>
     *
     * return $this
     */
    protected function replaceCalledProps()
    {
        foreach ($this->declaredProps as $prop) {
            $this->code = preg_replace(
                '/(\$this\-&gt;|' . $this->currentClass . '::\$|static::\$|self::\$)' . $prop . '(,|:|;|\)| |\.|\[|\]|\-)/',
                "$1<a href=\"$this->urlWithQuery#$prop\" role=\"link\">$prop</a>$2",
                $this->code
            );
        }
        return $this;
    }

    /**
     * Replace called methods.
     * $this->method, Class::method, static::method and self::method ->
     * ($this->|Class::|static::|self::)<a href="URL#method" role="link">method</a>
     *
     * return $this
     */
    protected function replaceCalledMethods()
    {
        foreach ($this->declaredMethods as $method) {
            $this->code = preg_replace(
                '/(\$this\-&gt;|' . $this->currentClass . '::|static::|self::)' . $method . '(\()/',
                "$1<a href=\"$this->urlWithQuery#$method\" role=\"link\">$method</a>$2",
                $this->code
            );
        }
        return $this;
    }

    /**
     * Replace called constants.
     * $this->const, Class::const, static::const and self::const ->
     * ($this->|Class::|static::|self::)<a href="URL#const" role="link">const</a>
     *
     * return $this
     */
    protected function replaceCalledConsts()
    {
        foreach ($this->declaredConsts as $const) {
            $this->code = preg_replace(
                '/(\$this\-&gt;|' . $this->currentClass . '::|static::|self::)' . $const . '(,|:|;|\)| |\.|\[|\]|\-)/',
                "$1<a href=\"$this->urlWithQuery#$const\" role=\"link\">$const</a>$2",
                $this->code
            );
        }
        return $this;
    }

    /**
     * Replace extended properties.
     *
     * return $this
     */
    protected function replaceExtendedProps()
    {
        preg_match_all(
            '/(this\-&gt;|' . $this->currentClass . '::\$|static::\$|self::\$)(\w+?)(,|:|;|\)| |\.|\[|\]|\-)/',
            $this->code,
            $matchExtendedProps
        );
        $extendedProps = array_unique($matchExtendedProps[2]);

        foreach ($extendedProps as $extendedProp) {
            foreach ($this->ancestorsAndTraits as $ancestorOrTrait) {
                if ($ancestorOrTrait->hasProperty($extendedProp)) {
                    $classWithNamespace = $ancestorOrTrait->getName();
                    $this->code = preg_replace(
                        '/(this\-&gt;|' . $this->currentClass . '::\$|static::\$|self::\$)' . $extendedProp . '(,|:|;|\)| |\.|\[|\]|\-)/',
                        "$1<a href=\"$this->urlWithoutQuery?q=$classWithNamespace#$extendedProp\" role=\"link\">$extendedProp</a>$2",
                        $this->code
                    );
                    break 1;
                }
            }
        }
        return $this;
    }

    /**
     * Replace extended methods.
     *
     * return $this
     */
    protected function replaceExtendedMethods()
    {
        preg_match_all(
            '/(this\-&gt;|' . $this->currentClass . '::|static::|self::)(\w+?)(\()/',
            $this->code,
            $matchExtendedMethods
        );
        $extendedMethods = array_unique($matchExtendedMethods[2]);

        foreach ($extendedMethods as $extendedMethod) {
            foreach ($this->ancestorsAndTraits as $ancestorOrTrait) {
                if ($ancestorOrTrait->hasMethod($extendedMethod)) {
                    $classWithNamespace = $ancestorOrTrait->getName();
                    $this->code = preg_replace(
                        '/(this\-&gt;|' . $this->currentClass . '::|static::|self::)' . $extendedMethod . '(\()/',
                        "$1<a href=\"$this->urlWithoutQuery?q=$classWithNamespace#$extendedMethod\" role=\"link\">$extendedMethod</a>$2",
                        $this->code
                    );
                    break 1;
                }
            }
        }
        return $this;
    }

    /**
     * Replace extended constants.
     *
     * return $this
     */
    protected function replaceExtendedConsts()
    {
        preg_match_all(
            '/(this\-&gt;|' . $this->currentClass . '::|static::|self::)(\w+?)(,|:|;|\)| |\.|\[|\]|\-)/',
            $this->code,
            $matchExtendedConsts
        );
        $extendedConsts = array_unique($matchExtendedConsts[2]);

        foreach ($extendedConsts as $extendedConst) {
            foreach ($this->ancestorsAndTraits as $ancestorOrTrait) {
                if ($ancestorOrTrait->hasProperty($extendedConst)) {
                    $classWithNamespace = $ancestorOrTrait->getName();
                    $this->code = preg_replace(
                        '/(this\-&gt;|' . $this->currentClass . '::|static::|self::)' . $extendedConst . '(,|:|;|\)| |\.|\[|\]|\-)/',
                        "$1<a href=\"$this->urlWithoutQuery?q=$classWithNamespace#$extendedConst\" role=\"link\">$extendedConst</a>$2",
                        $this->code
                    );
                    break 1;
                }
            }
        }
        return $this;
    }

    /**
     * Replace parent.
     *
     * parent to <a href="Url?q=Namespace\Parent">parent</a>
     *
     * return $this
     */
    protected function replaceParentPropsMethodsConsts()
    {
        if (!empty($this->parentClass)) {
            if (isset($this->classArr[$this->parentClass])) {
                $parentWithNamespace = $this->classArr[$this->parentClass];
            } else {
                try {
                    $parentWithNamespace = (new ReflectionClass($this->namespace . '\\' . $this->parentClass))->getName();
                } catch (Exception $e) {}
            }

            //parent::
            $this->code = preg_replace(
                '/(?<!\w)parent::/',
                "<a href=\"$this->urlWithoutQuery?q=$parentWithNamespace\">parent</a>::",
                $this->code
            );

            //parent::props, methods, and consts
            $this->code = preg_replace(
                '/parent<\/a>::(\$)?(\w+)/',
                "parent</a>::<a href=\"$this->urlWithoutQuery?q=$parentWithNamespace#$2\" role=\"link\">$1$2</a>",
                $this->code
            );
        }
        return $this;
    }

    /**
     * Replace internal functions.
     * 
     * return $this
     */
    protected function replaceInternalFunctions()
    {
        preg_match_all('/(\(|\[| )([a-z]\w+)\(/', $this->code, $matches);
        $funcs = array_unique($matches[2]);
        foreach($funcs as $func) {
            if(function_exists($func) && (new ReflectionFunction($func))->isInternal()) {
                $replacedFunc = str_replace('_', '-', $func);
                preg_match_all('/(\(|\[| )' . '(' . $func . ')(\()/', $this->code, $match);
                
                $this->code = preg_replace(
                    '/(\(|\[| )' . '(' . $func . ')(\()/',
                    '$1' . '<a href="https://www.php.net/manual/ja/function.' . $replacedFunc . '.php">' . $func . '</a>$3',
                    $this->code);
            }
        }

        return $this;
    }

    /**
     * Create html table is inserted code.
     *
     * return void
     */
    protected function code2Table()
    {
        $lines = explode("\n", $this->code);
        $i = 1;
        $code = "";
        foreach ($lines as $line) {
            $code .= '<tr><td><pre><span class="num">' . ($i++) . '<span></pre></td><td><pre>' . $line . '</pre></td></tr>';
        }
        $this->code = "<table><tbody>$code</tbody></table>";
    }

    /**
     * Set html string of file list.
     *
     * return void
     */
    protected function setFileList()
    {
        $fileList = "";
        foreach (glob(dirname($this->currentFilePath) . '/*.php') as $filePath) {
            $baseName = basename($filePath);
            $fileList .= "<div><pre><span>    </span><a href=\"$this->urlWithoutQuery?q=$filePath\">$baseName</a></pre></div>\n";
        }

        $this->fileList = <<<LIST
<div class="toggle">
        <span>Files</span>
        <span class="toggle">
            <span class="icon-chevron-right"></span>
        </span>
</div>
<div class="phpfiles" style="display: none;">
    $fileList
</div>
LIST;
    }

    /**
     * Creat html script of javascript.
     *
     * return string
     */
    protected function jsLink()
    {
        return "<script type=\"text/javascript\" src=\"$this->jsPath\"></script>";
    }

    /**
     * Output html.
     *
     * @param string $parts
     * return void
     */
    public function output()
    {
        echo $this->html;
    }
}
