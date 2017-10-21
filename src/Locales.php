<?php

namespace System;

use Locale;
use Pirate\Hooray\Arr;
use Pirate\Hooray\Str;
use InvalidArgumentException;
use System\Locales\Available;

class Locales
{

    /**
     * Locale setting categories
     * + `all` (`LC_ALL`)
     * + `collate` (`LC_COLLATE`)
     * + `ctype` (`LC_CTYPE`)
     * + `monetary` (`LC_MONETARY`)
     * + `numeric` (`LC_NUMERIC`)
     * + `time` (`LC_TIME`)
     * + `messages` (`LC_MESSAGES`)
     * @var string[]
     */
    const CATEGORIES = [
        'all',
        'collate',
        'ctype',
        'monetary',
        'numeric',
        'time',
        'messages',
    ];

    private static $directory;
    private static $index;
    private static $categories;
    private static $initialized = false;

    /**
     * Search for installed localed and create a PHP class file
     */
    public static function postUpdate()
    {
        $classdir = __DIR__ . DIRECTORY_SEPARATOR . 'Locales';
        $classfile = $classdir . DIRECTORY_SEPARATOR . 'Available.php';

        if (file_exists($classfile)) {
            if (unlink($classfile) === false) {
                die("cannot unlink class file $classfile");
            }
        } elseif (!file_exists($classdir)) {
            if (mkdir($classdir) === false) {
                die("cannot create class directory $classdir");
            }
        }

        $output = shell_exec('locale -a');

        if (!Str::ok($output)) {
            die("cannot execute `locale -a`");
        }

        $locales = Str::matchall($output, '/\S+/');

        $array = var_export($locales[0], true);

        $content = '<?php namespace System\Locales; class Available { const Installed = ' . $array . '; }' . PHP_EOL;

        if (file_put_contents($classfile, $content) === false) {
            die("cannot write class file $classfile");
        }
    }

    /**
     * Initialize index arrays
     */
    public static function initialize()
    {
        if (self::$initialized === true) {
            return;
        }
        self::$directory = [];
        self::$index = [];
        self::$categories = [];
        foreach (Available::Installed as $locale) {
            $elements = Locale::parseLocale($locale);
            foreach ($elements as $key => $value) {
                Arr::init(self::$index, $key, []);
                Arr::init(self::$index[$key], $value, []);
                self::$index[$key][$value][] = $locale;
            }
            self::$directory[$locale] = $elements;
        }
        foreach (self::CATEGORIES as $name) {
            $category = (int) constant('LC_' . strtoupper($name));
            self::$categories[$name] = $category;
        }
        self::$initialized = true;
    }

    /**
     * Normalize a locale string
     * @param string $locale
     * @return string
     */
    public static function normalize(string $locale)
    {
        self::initialize();
        return Locale::composeLocale(Locale::parseLocale($locale));
    }

    /**
     * Lookup a specific locale in the index directory
     * @param string $locale
     * @return mixed
     */
    public static function lookup(string $locale)
    {
        self::initialize();
        return Arr::get(self::$directory, $locale);
    }

    /**
     * Search for an installed locale
     * @param string $have A locale part name which we have (`language`, `region`)
     * @param string|null $query The value of the part we have. If null, returns all locales with the meaning of $have as keys.
     * @param string|null $want The locale part we are searching for (`language`, `region`). Must be distinct from $have. If null, returns all locales to $query.
     * @return array|null
     */
    public static function search(string $have, string $query = null, string $want = null)
    {
        self::initialize();
        if ($have === $want) {
            throw new InvalidArgumentException('$have parameter must be distinct from $want parameter');
        }
        $subIndex = Arr::get(self::$index, $have);
        if (!Arr::ok($subIndex)) {
            return null;
        }
        if (is_null($query)) {
            return $subIndex;
        }
        $locales = Arr::get($subIndex, $query);
        if (is_null($locales)) {
            return null;
        }
        if (is_null($want)) {
            return $locales;
        }
        $map = [];
        foreach ($locales as $id) {
            $locale = Arr::get(self::$directory, $id);
            $value = Arr::get($locale, $want);
            if (!is_null($value)) {
                $map[$value][] = $id;
            }
        }
        return $map;
    }

    /**
     * Get a current locale setting
     * @param int|string $category integer (from `LC_*` constant) or a category name
     * @see self::CATEGORIES
     * @return string
     */
    public static function get($category)
    {
        self::initialize();
        if (is_string($category)) {
            $category = self::$categories[$category];
        }
        return setlocale($category, 0);
    }

    /**
     * Set a locale setting
     * @param int|string $category integer (from `LC_*` constant) or a category name
     * @param $value
     * @see self::CATEGORIES
     */
    public static function set($category, $value)
    {
        self::initialize();
        if (is_string($category)) {
            $category = self::$categories[$category];
        }
        setlocale($category, $value);
    }

    /**
     * Encapsulate a codeblock with locale settings valid only for this call
     * The locale settings will be resetted even in case of an exception
     * @param string|array $locale
     * @param callable $function
     * @return mixed return value of $function
     */
    public static function wrap($locale, callable $function)
    {
        self::initialize();
        $safe = [];
        if (!is_array($locale)) {
            $locale = ['all' => $locale];
        }
        try {
            foreach ($locale as $category => $value) {
                $orig = self::get($category);
                self::set($category, $value);
                $safe[$category] = $orig;
            }
            return $function();
        } finally {
            foreach ($safe as $category => $value) {
                self::set($category, $value);
            }
        }
    }

    /**
     * Call `iconv` with specific character type, only valid for this call
     * @see self::wrap()
     * @see \iconv()
     * @param string $ctype character type (`LC_CTYPE`)
     * @param string $in_charset
     * @param string $out_charset
     * @param string $str
     * @return mixed
     */
    public static function iconv(string $ctype, string $in_charset, string $out_charset, string $str)
    {
        self::initialize();
        return self::wrap(
            ['ctype' => $ctype],
            function () use ($in_charset, $out_charset, $str) {
                return iconv($in_charset, $out_charset, $str);
            }
        );
    }
}
