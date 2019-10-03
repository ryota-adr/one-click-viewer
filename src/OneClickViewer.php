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
    protected $required = [];

    /**
     * @var string[]
     */
    protected $requiredAutoloaders = [];

    /**
     * Html head tag.
     *
     * @var string
     */
    protected $head = '';

    /**
     * Fully qualified class name or path from query string.
     *
     * @var string
     */
    protected $classNameOrPath = '';

    /**
     * Url of this viewer with query string.
     *
     * @var string
     */
    protected $urlWithQuery = '';

    /**
     * Current php file path.
     *
     * @var string
     */
    protected $currentFilePath = '';

    /**
     * Current php code.
     *
     * @var string
     */
    protected $code = '';

    /**
     * Current class name.
     */
    protected $currentClass;

    /**
     * Parent class of current class.
     *
     * @var string
     */
    protected $parentClass = '';

    /**
     * Current namespace.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * The array of ancestors and traits.
     *
     * @var \ReflectionClass[]
     */
    protected $ancestorsAndTraits = [];

    protected $classArrs = [];

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
    protected $dirUri = '';

    /**
     * Html string of file link list.
     *
     * @var string
     */
    protected $fileList = '';

    /**
     * Html form tag.
     *
     * @var string
     */
    protected $form = '';

    protected $inputValue = '';

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
    public function __construct($envPath, $classNameOrPath)
    {
        $this->setAutoloaderPaths($envPath);

        $this->urlWithQuery = APP_HOST . '/?q=' . $classNameOrPath;

        $this->requireAutoloader($this->autoloaders);
        
        if (isset($classNameOrPath)) {
            $this->classNameOrPath = $classNameOrPath;
            $this->setCode();
            $this->setCurrentClass();
            $this->setNamespace();
            $this->setInputValue();
        } else {
            $this->occuredError = true;
            $this->setInputValue();
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
     * Require autoloader.php.
     *
     * @param string[] $autoloaders
     * return void
     */
    protected function requireAutoloader($autoloaders)
    {
        foreach ($autoloaders as $autoloader) {
            if (file_exists($autoloader)) {
                require $autoloader;
                $this->required[] = [
                    'bool' => true,
                    'path' => $autoloader
                ];
                $this->requiredAutoloaders[] = $autoloader;
            } else {
                $this->required[] = [
                    'bool' => false,
                    'path' => $autoloader
                ];
            }
        }
        
        foreach ($this->required as $required) {
            if ($required['bool'] === false) {
                $this->message = 'invalid autoloader file path: ' . $required['path'];
                $this->occuredError = true;
                break;
            }
        }
    }

    public function getInputValue()
    {
        return $this->inputValue;
    }

    protected function setInputValue()
    {
        $phpSelf = basename($_SERVER['PHP_SELF']);
        $this->inputValue = (!empty($this->namespace) && !empty($this->currentClass)) ? $this->namespace . '\\' . $this->currentClass : $this->classNameOrPath;
    }

    /**
     * make html to output.
     *
     * return $this
     */
    public function setHtml()
    {
        if (!$this->occuredError) {
            $this->setParentClass();
            $this->setClassArrs($this->code);
            $this->setAncestorsAndTraits();
            $this->setDeclaredProps();
            $this->setDeclaredMethods();
            $this->setDeclaredConsts();

            $this
                ->replaceClasses($this->code, $this->classArrs)
                ->addSpanIds()
                ->replaceCalledProps()
                ->replaceCalledMethods()
                ->replaceCalledConsts()
                ->replaceExtendedProps()
                ->replaceExtendedMethods()
                ->replaceExtendedConsts()
                ->replaceParentPropsMethodsConsts()
                ->replaceInternalFunctions()
                ->replaceInternalClasses();

            $this->code2Table();
            $this->setFileList();

            $this->html = <<<BODY
<div>
    <div class="namespace">
        $this->form
    </div>
    <div class="dir">
        $this->dirUri
    </div>
    <div>
        $this->fileList
    </div>
    <div>
        $this->code
    </div>
</div>
BODY;
        } else {
            $form = $this->form;
            $this->html = <<<BODY
<div class="container">
    <div class="message">
       $this->message
    </div>
</div>
BODY;
        }

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
                    $this->currentFilePath = str_replace('\\', '/', $this->classNameOrPath);
                    $this->code = htmlspecialchars(file_get_contents($this->currentFilePath));
                } else { //fully qualified name
                    $this->currentFilePath = str_replace('\\', '/', (new ReflectionClass($this->classNameOrPath))->getFileName());
                    $this->code = htmlspecialchars(@file_get_contents($this->currentFilePath));
                }
            } catch (Exception $e) {
                $this->occuredError = true;
                $this->message = "Invalid class name or path.";
            }
        }
    }

    public function getDirUri()
    {
        return dirname($this->currentFilePath);
    }

    /**
     * Set current class name.
     *
     * return void
     */
    protected function setCurrentClass()
    {
        preg_match('/^(class|interface|abstract class|trait|final class) (\w+)/m', $this->code, $match_class);
        
        $this->currentClass = isset($match_class[2]) ? $match_class[2] : '';
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
        if (!(empty($this->namespace) || empty($this->currentClass))) {
            try {
                $reflectionThisClass = new ReflectionClass($this->namespace . '\\' . $this->currentClass);
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
            } catch (Exception $e) {}
        }
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

    protected function setClassArrs($code)
    {
        preg_match_all('/\\\?(((\w+\\\)+\w+)( as (\w+))?)/', $code, $matchClasses);
        $classes = array_unique($matchClasses[1]);
        $filteredClasses = array_filter($classes, function($class) use ($classes) {
            return preg_grep('/' . preg_quote($class) . ' as \w+/', $classes) ? false : true;
        });

        $classArrs = [];
        foreach ($filteredClasses as $class) {
            preg_match('/ as (\w+)/', $class, $matchAsAlias);

            if ($matchAsAlias) {
                $fullyQualifiedClassName = str_replace($matchAsAlias[0], '', $class);
                $explodedByBackslash = explode('\\', $fullyQualifiedClassName);
                $end = end($explodedByBackslash);
                $classArrs[] = [
                    'fullyQualifiedClassName' => $fullyQualifiedClassName,
                    'end' => $end,
                    'alias' => $matchAsAlias[1]
                ];
            } else {
                $explodedByBackslash = explode('\\', $class);
                $end = end($explodedByBackslash);
                $classArrs[] = [
                    'fullyQualifiedClassName' => $class,
                    'end' => $end,
                    'alias' => ''
                ];
            }
        }
        $classArrs = $this->addSameDirClasses($classArrs);

        $this->classArrs = $classArrs;
    }

    protected function addSameDirClasses($classArrs)
    {
        if (empty($this->namespace)) {
            return $classArrs;
        }

        $sameDirClasses = array_map(function($path) {
            return str_replace('.php', '', str_replace(dirname($this->currentFilePath) . '/' , '', $path));
        }, glob(dirname($this->currentFilePath) . '/*.php'));

        foreach ($sameDirClasses as $class) {
            $classArrs[] = [
                'fullyQualifiedClassName' => $this->namespace . '\\' . $class,
                'end' => $class,
                'alias' => ''
            ];
        }
        return $classArrs;
    }

    protected function replaceClasses($code, $classArrs)
    {
        $this->code = $this->replaceFullyQualifiedClassName($code, $classArrs);
        $this->code = $this->replaceAliasClass($this->code, $classArrs);
        $this->code = $this->replaceEndClass($this->code, $classArrs);
        $this->code = $this->replaceNotFullyQualifiedClassName($this->code, $classArrs);
        $this->code = $this->replaceClassesWithoutNamespace($this->code);

        return $this;
    }

    protected function replaceFullyQualifiedClassName($code, $classArrs)
    {
        foreach ($classArrs as $classArr) {
            try {
                $fullyQualifiedClassName = $classArr['fullyQualifiedClassName'];
                (new ReflectionClass($fullyQualifiedClassName))->getFileName();
                $code = preg_replace(
                    '/(?<![\w|>])' . preg_quote($fullyQualifiedClassName) . '(?![\s]as[\s]\w+|\\\|\w)/',
                    '<a href="' . APP_HOST . '/?q=' . $fullyQualifiedClassName . '" role="link">' . $fullyQualifiedClassName . '</a>', 
                    $code
                );
            } catch (Exception $e) {}
        }

        return $code;
    }

    protected function replaceAliasClass($code, $classArrs)
    {
        $hasAliasArrs = array_filter($classArrs, function($class) {
            return !empty($class['alias']);
        });

        foreach ($hasAliasArrs as $classArr) {
            try {
                $fullyQualifiedClassName = $classArr['fullyQualifiedClassName'];
                $alias = $classArr['alias'];

                (new ReflectionClass($fullyQualifiedClassName))->getFileName();
                $code = preg_replace(
                    '/(?<![\w>])' . preg_quote($fullyQualifiedClassName) . '[\s]as[\s]\w+/',
                    '<a href="' . APP_HOST . '/?q=' . $fullyQualifiedClassName . '" role="link">' . $fullyQualifiedClassName . ' as ' . $alias . '</a>', 
                    $code
                );

                $code = preg_replace(
                    '/(?<!class | trait |interface |[\w\\\>])' . $alias . '(?![\w\\\<])/',
                   '<a href="' . APP_HOST . '/?q=' . $fullyQualifiedClassName . '" role="link">' . $alias . '</a>',
                   $code
                );
            } catch (Exception $e) {}
        }

        return $code;
    }

    protected function replaceEndClass($code, $classArrs)
    {
        foreach ($classArrs as $classArr) {
            $code = preg_replace(
                '/(?<!class |trait |interface |[\w\\\>])' . $classArr['end'] . '(?![\w\\\<])/',
                '<a href="' . APP_HOST . '/?q=' . $classArr['fullyQualifiedClassName'] . '" role="link">' . $classArr['end'] . '</a>',
                $code
            );
        }
        return $code;
    }

    protected function replaceNotFullyQualifiedClassName($code)
    {
        preg_match_all('/use \\\?([\w+\\\]+\w);/', $code, $match);
        foreach ($match[1] as $namespace) {
            try {
                (new ReflectionClass($namespace))->getFileName();
            } catch (Exception $e) {
                $exploded = explode('\\', $namespace);
                $end = end($exploded);
                preg_match_all('/' . $end . '\\\[A-z_]+/', $code, $matchNotClass);
            }

            if (!isset($matchNotClass[0])) {
                continue;
            }

            foreach ($matchNotClass[0] as $notFullyQualifiedClass) {
                $fullyQualifiedClass = str_replace($end, $notFullyQualifiedClass, $namespace);
                try {
                    (new ReflectionClass($fullyQualifiedClass))->getFileName();

                    $code = preg_replace(
                        '/(?<!\w[\\\>])' . preg_quote($notFullyQualifiedClass) . '(?![\w<])/',
                        '<a href="' . APP_HOST . '/?q=' . $fullyQualifiedClass . '" role="link">' . $notFullyQualifiedClass . '</a>',
                        $code
                    );
                } catch (Exception $e) {}
            }
        }

        return $code;
    }

    protected function replaceClassesWithoutNamespace($code)
    {
        $classes = $this->getClassesWithoutNamespace($code);
        
        foreach ($classes as $class) {
            $code = preg_replace(
                '/(?<!class |\w\\\|<s>)' . $class . '(?![A-Za-z_]|<\/a>)/',
                '<a href="' . APP_HOST . "/?q=$class\" role=\"link\">$class</a>",
                $code
            );
        }
        return $code;
    }

    protected function getClassesWithoutNamespace($code)
    {
        preg_match_all('/(?<!class |\w\\\|>)([A-Za-z]+_)+[A-Za-z]+/', $code, $matchClasses);
        $classes = array_filter($matchClasses[0], function($class) {
            try {
                return (new ReflectionClass($class))->getFileName();
            } catch (Exception $e) {
                return false;
            }
        });

        return array_unique($classes);
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
                        '$1<a href="' . APP_HOST . "/?q=$classWithNamespace#$extendedProp\" role=\"link\">$extendedProp</a>$2",
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
                        "$1<a href=\"" . APP_HOST . "/?q=$classWithNamespace#$extendedMethod\" role=\"link\">$extendedMethod</a>$2",
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
                if ($ancestorOrTrait->hasConstant($extendedConst)) {
                    $classWithNamespace = $ancestorOrTrait->getName();
                    $this->code = preg_replace(
                        '/(' . $this->currentClass . '::|static::|self::)' . $extendedConst . '(,|:|;|\)| |\.|\[|\]|\-)/',
                        "$1<a href=\"" . APP_HOST . "/?q=$classWithNamespace#$extendedConst\" role=\"link\">$extendedConst</a>$2",
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
            for($i = 0; $i < count($this->classArrs); $i++) {
                if ($this->classArrs[$i]['end'] === $this->parentClass ||
                    $this->classArrs[$i]['alias'] === $this->parentClass) {
                    $fullyQualifiedParentClassName = $this->classArrs[$i]['fullyQualifiedClassName'];
                    break;
                }
            }

            if (!isset($fullyQualifiedParentClassName)) {
                try {
                    $fullyQualifiedParentClassName = (new ReflectionClass($this->namespace . '\\' . $this->parentClass))->getName();
                } catch (Exception $e) {
                    return $this;
                }
            }
            
            //parent::
            $this->code = preg_replace(
                '/(?<![\w\\\>])parent(?![\w<])/',
                '<a href="' . APP_HOST . "/?q=$fullyQualifiedParentClassName\">parent</a>",
                $this->code
            );

            //parent::props, methods, and consts
            $this->code = preg_replace(
                '/parent<\/a>::(\$)?(\w+)/',
                'parent</a>::<a href="' . APP_HOST . "/?q=$fullyQualifiedParentClassName#$2\" role=\"link\">$1$2</a>",
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
                    '$1' . '<a href="https://www.php.net/manual/ja/function.' . $replacedFunc . '.php" role="link">' . $func . '</a>$3',
                    $this->code);
            }
        }

        return $this;
    }

    /**
     * Replace internal classes.
     * 
     * return $this
     */
    protected function replaceInternalClasses()
    {
        preg_match_all('/(?<=use |new | \\\|\(|\[)(([A-Z][a-z]+)+)(?![\\\<])/', $this->code, $matchNoNamespaceClassName);
        $matchNoNamespaceClassNames = array_unique($matchNoNamespaceClassName[0]);
        
        foreach ($matchNoNamespaceClassNames as $noNamespaceClassName) {
            try {
                if ((new ReflectionClass($noNamespaceClassName))->isInternal()) {
                    $this->code = preg_replace(
                        '/(?<!\w|\w\\\|>)' . $noNamespaceClassName . '(?!\w|\\\\w|<)/',
                        '<a href="https://www.php.net/manual/ja/class.' . strtolower($noNamespaceClassName) . '.php" role="link">' . $noNamespaceClassName . '</a>',
                        $this->code
                    );
                }
            } catch (Exception $e) {}
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
            $code .= '<tr><td><div><span class="num">' . ($i++) . '<span></div></td><td><div>' . $line . '</div></td></tr>';
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
            $fileList .= '<div><span>    </span><a href="' . APP_HOST . "/?q=$filePath\">$baseName</a></div>\n";
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
     * Get html string.
     * 
     * return string.
     */
    public function getHtml()
    {
        return $this->html;
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
