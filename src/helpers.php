<?php

if (!function_exists('user')) {
    /**
     * @return App\User
     */
    function user($guard = null)
    {
        return auth($guard)->user();
    }
}

if (!function_exists('json_parse')) {
    /** @return array */
    function json_parse($data = null, $default = null, $asObject = false)
    {
        try {
            return json_decode($data, !$asObject);
        } catch (Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('json_stringify')) {
    /** @return string */
    function json_stringify($data, $pretty = false)
    {
        $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        if ($pretty) {
            $options = $options | JSON_PRETTY_PRINT;
        }

        return json_encode($data, $options);
    }
}

if (!function_exists('keystoupper')) {
    /** @return array */
    function keystoupper(array $array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $key = strtoupper($key);
            $result[$key] = $value;
        }

        return $result;
    }
}

if (!function_exists('chance')) {
    function chance($percent = 50, $max = 100, $min = 1)
    {
        return mt_rand($min, $max) <= $percent;
    }
}

if (!function_exists('upload_limit')) {
    function upload_limit($mb = false)
    {
        $size = min([
            filesize_parse(ini_get('upload_max_filesize')),
            filesize_parse(ini_get('post_max_size')),
            filesize_parse(ini_get('memory_limit')),
        ]);

        return $mb ? floor($size / 1024 / 1024) : $size;
    }
}

if (!function_exists('replace_newlines')) {
    function replace_newlines($string, $symbol = "\n")
    {
        return str_replace(["\r\n", "\r", "\n"], $symbol, $string);
    }
}

if (!function_exists('str_between')) {
    function str_between($subject, $after, $before)
    {
        $subject = str_after($subject, $after);
        $subject = str_before($subject, $before);

        return $subject;
    }
}

if (!function_exists('str_uuid')) {
    function str_uuid($ordered = false)
    {
        $uuid = $ordered ? Illuminate\Support\Str::orderedUuid() : Illuminate\Support\Str::uuid();

        return (string) $uuid;
    }
}

if (!function_exists('list_cleanup')) {
    function list_cleanup($array, $callback = null, $arguments = [])
    {
        if ($array instanceof \Illuminate\Support\Collection) {
            $array = $array->all();
        }
        if ($callback) {
            $array = array_map_args($array, $callback, $arguments);
        }
        $array = array_filter($array);
        $array = array_unique($array);

        return array_values($array);
    }
}

if (!function_exists('array_map_args')) {
    function array_map_args(array $array, $callback, $arguments = [])
    {
        array_unshift($arguments, null);
        foreach ($array as &$element) {
            $arguments[0] = $element;
            $element = call_user_func_array($callback, $arguments);
        }

        return $array;
    }
}

if (!function_exists('url_encode')) {
    function url_encode($url)
    {
        $url = url_decode($url);

        return urlencode($url);
    }
}

if (!function_exists('url_decode')) {
    function url_decode($url)
    {
        $before = null;
        while ($before != $url) {
            $before = $url;
            $url = urldecode($url);
        }

        return $url;
    }
}

if (!function_exists('url_parse')) {
    function url_normalize($url, $withoutFragment = false, $withoutQuery = false)
    {
        if (!str_contains($url, '//')) {
            $url = '//'.$url;
        }
        $parts = parse_url($url);
        $host = array_get($parts, 'host');
        if (!$host) {
            return;
        }
        if (extension_loaded('intl')) {
            $host = idn_to_utf8($host);
        }
        $scheme = array_get($parts, 'scheme', 'http').'://';
        $path = array_get($parts, 'path', '/');
        $query = $withoutQuery ? null : array_get($parts, 'query');
        if ($query) {
            parse_str($query, $query);
            $query = '?'.http_build_query($query);
        }
        $fragment = $withoutFragment ? null : array_get($parts, 'fragment');
        if ($fragment) {
            $fragment = '#'.$fragment;
        }

        return implode('', [$scheme, $host, $path, $query, $fragment]);
    }
}

if (!function_exists('url_domain')) {
    function url_domain($url)
    {
        if (str_contains($url, '/')) {
            $url = parse_url($url);

            return $url['host'] ?? '';
        }

        return $url;
    }
}

if (!function_exists('build_url')) {
    function build_url($parsed_url)
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':'.$parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':'.$parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?'.$parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#'.$parsed_url['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}

if (!function_exists('query')) {
    function query($params, $url = null)
    {
        $url = $url ?: request()->fullUrl();
        $url = parse_url($url);
        parse_str($url['query'] ?? '', $query);
        $params = array_merge($query, $params);
        $url['query'] = http_build_query($params);

        return build_url($url);
    }
}

if (!function_exists('dj')) {
    function dj($data)
    {
        header('Content-Type: application/json');
        echo json_stringify($data, true);

        die(1);
    }
}

if (!function_exists('css')) {
    function css($name)
    {
        return asset_mix("css/$name.css");
    }
}

if (!function_exists('js')) {
    function js($name)
    {
        return asset_mix("js/$name.js");
    }
}

if (!function_exists('asset_mix')) {
    function asset_mix($file)
    {
        try {
            return url(mix($file));
        } catch (Exception $exception) {
            $messages = [
                'The Mix manifest does not exist',
                'Unable to locate Mix file',
            ];
            if (starts_with($exception->getMessage(), $messages)) {
                return asset($file);
            }

            throw $exception;
        }
    }
}

if (!function_exists('img')) {
    function img($file)
    {
        return asset("img/$file");
    }
}

if (!function_exists('array_avg')) {
    function array_avg(array $array)
    {
        $sum = array_sum($array);
        $count = count($array);

        return div($sum, $count);
    }
}

if (!function_exists('div')) {
    function div($divisible, $divisor, $number = false)
    {
        $null = $number ? 0 : null;

        return $divisor ? $divisible / $divisor : $null;
    }
}

if (!function_exists('array_flip_multiple')) {
    function array_flip_multiple(array $array)
    {
        $result = [];

        foreach ($array as $key => $value) {
            $result[$value][] = $key;
        }

        return $result;
    }
}

if (!function_exists('is_assoc')) {
    function is_assoc($array)
    {
        if ([] === $array) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }
}

if (!function_exists('microsleep')) {
    function microsleep($micro_seconds)
    {
        usleep($micro_seconds * 1000000);
    }
}

if (!function_exists('filename_normalize')) {
    function filename_normalize($name, $spaces = ' ')
    {
        $name = mb_ereg_replace("\s+", $spaces, $name);
        $name = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $name);
        $name = mb_ereg_replace("([\.]{2,})", '', $name);

        return $name;
    }
}

if (!function_exists('float')) {
    function float($number)
    {
        $number = str_replace(',', '.', $number);

        //$number = preg_replace('/\s+/', '', $number);
        return (float) $number;
    }
}

if (!function_exists('checkbox')) {
    function checkbox($value, $default = false)
    {
        if (is_bool($value)) {
            return $value;
        }
        if (in_array($value, ['true', 'on', 'yes', '1', 1], true)) {
            return true;
        }
        if (in_array($value, ['false', 'off', 'no', '0', 0], true)) {
            return false;
        }

        return $default;
    }
}

if (!function_exists('digits')) {
    function digits($string)
    {
        return preg_replace('/[^\d]/', '', $string);
    }
}

if (!function_exists('email_normalize')) {
    function email_normalize($address)
    {
        if (!str_contains($address, '@') || mb_strlen($address) < 7) {
            return;
        }
        $address = trim($address);
        $address = mb_strtolower($address);

        return $address;
    }
}

if (!function_exists('paginate')) {
    function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Illuminate\Support\Collection ? $items : collect($items);

        return new Illuminate\Pagination\LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}

if (!function_exists('carbon')) {
    /** @return \Carbon\Carbon */
    function carbon($date = null)
    {
        if (is_numeric($date)) {
            return \Date::createFromTimestamp($date);
        }

        return \Date::parse($date);
    }
}

if (!function_exists('cache_remember')) {
    function cache_remember($key, $minutes, $callback, array $arguments = [], $driver = null)
    {
        $hash = array_hash($arguments);

        return Cache::driver($driver)->remember("$key.$hash", $minutes, function () use ($callback, $arguments) {
            return call_user_func_array($callback, $arguments);
        });
    }
}

if (!function_exists('number')) {
    function number($number, $decimals = 0, $units = null, $separator = null, $plus = false)
    {
        $negative = $number < 0;
        $number = abs($number);
        $separator = $separator ?? uchr(160);
        $result = number_format($number, $decimals, ',', $separator);

        if ($units) {
            if (str_contains($units, '|')) {
                $units = explode('|', $units);
                if (($number - $number % 10) % 100 != 10) {
                    if ($number % 10 == 1) {
                        $units = $units[0].$units[2];
                    } elseif ($number % 10 >= 2 && $number % 10 <= 4) {
                        $units = $units[0].$units[3];
                    } else {
                        $units = $units[0].$units[1];
                    }
                } else {
                    $units = $units[0].$units[1];
                }
            }
            $result .= $separator.$units;
        }

        if ($negative) {
            $result = '−'.$separator.$result;
        } elseif ($plus) {
            $result = '+'.$separator.$result;
        }

        return $result;
    }
}

if (!function_exists('filesize_units')) {
    function filesize_units($locale = null)
    {
        return array_get([
            'en' => ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
            'ru' => ['Б', 'КБ', 'МБ', 'ГБ', 'ТБ', 'ПБ', 'ЭБ', 'ЗБ', 'ИБ'],
        ], $locale, []);
    }
}

if (!function_exists('filesize_format')) {
    function filesize_format($size, $locale = 'en')
    {
        $units = filesize_units($locale);
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        $number = $size / pow(1024, $power);

        return number($number, 1, $units[$power]);
    }
}

if (!function_exists('filesize_parse')) {
    function filesize_parse($string, $locale = 'en')
    {
        $units = filesize_units($locale);
        $byte = $units[0];

        preg_match('/^([\d,\.]+)\s*(.*)$/u', $string, $matches);

        if (!$matches) {
            return;
        }

        $number = float($matches[1]);
        $unit = strtoupper($matches[2] ?: $byte);

        if (!ends_with($unit, $byte)) {
            $unit = "$unit$byte";
        }

        foreach ($units as $pow => $name) {
            if ($unit == $name) {
                return $number * pow(1024, $pow);
            }
        }
    }
}

if (!function_exists('name_initials')) {
    function name_initials($fullname, $separator = null, $short = false)
    {
        $separator = $separator ?? uchr(160);
        $parts = explode(' ', $fullname);
        $result = [];
        $result[] = array_shift($parts);
        foreach ($parts as $part) {
            $result[] = mb_substr($part, 0, 1).'.'.($short ? '' : ' ');
        }
        $result = implode(' ', $result);
        $result = str_replace(' ', $separator, $result);

        return trim($result);
    }
}

if (!function_exists('markdown')) {
    function markdown(
        $content,
        $text_mode = false,
        $disable_breaks = false,
        $ignore_links = false,
        $escape_html = false
    ) {
        $markdown = Parsedown::instance()
            ->setBreaksEnabled(!$disable_breaks)
            ->setUrlsLinked(!$ignore_links)
            ->setMarkupEscaped($escape_html);
        $markdown = $text_mode ? $markdown->text($content) : $markdown->line($content);

        return new Illuminate\Support\HtmlString($markdown);
    }
}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($str)
    {
        $fc = mb_strtoupper(mb_substr($str, 0, 1));

        return $fc.mb_substr($str, 1);
    }
}

if (!function_exists('str_limit_hard')) {
    function str_limit_hard($value, $limit = 100, $end = '...')
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        $limit -= mb_strwidth($end, 'UTF-8');
        if ($limit < 0) {
            return '';
        }

        return str_limit($value, $limit, $end);
    }
}

if (!function_exists('language')) {
    function language($locale = null)
    {
        $locales = [
            'en' => 'en_US',
            'ru' => 'ru_RU',
        ];

        $locale = $locale ?: config('app.locale');
        $default = config('app.fallback_locale');

        return array_get($locales, $locale) ?: array_get($locales, $default);
    }
}

if (!function_exists('whitespaces')) {
    function whitespaces($string, $newlines = false, $multilines = false)
    {
        if ($newlines) {
            $string = replace_newlines($string);
            $string = preg_replace('/[^\S\n]+/', ' ', $string);
            if (!$multilines) {
                $string = preg_replace('/\n+/', "\n", $string);
            }
        } else {
            $string = preg_replace('/\s+/', ' ', $string);
        }

        return $string;
    }
}

if (!function_exists('multiexplode')) {
    function multiexplode($delimiters, $string)
    {
        if (is_string($delimiters)) {
            $delimiters = str_split($delimiters);
        }
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);

        return $launch;
    }
}

if (!function_exists('uchr')) {
    function uchr($codes)
    {
        if (is_scalar($codes)) {
            $codes = func_get_args();
        }

        $str = '';
        foreach ($codes as $code) {
            $str .= html_entity_decode('&#'.$code.';', ENT_NOQUOTES, 'UTF-8');
        }

        return $str;
    }
}

if (!function_exists('uord')) {
    function uord($symbol)
    {
        $k = mb_convert_encoding($symbol, 'UCS-2LE', 'UTF-8');
        $k1 = ord(substr($k, 0, 1));
        $k2 = ord(substr($k, 1, 1));

        return $k2 * 256 + $k1;
    }
}

if (!function_exists('string2binary')) {
    function string2binary($string)
    {
        $chars = str_split($string);
        foreach ($chars as &$char) {
            $char = decbin(ord($char));
            $char = str_pad($char, 8, 0, STR_PAD_LEFT);
        }

        return implode('', $chars);
    }
}

if (!function_exists('binary2string')) {
    function binary2string($binary)
    {
        $chars = str_split($binary, 8);
        foreach ($chars as &$char) {
            $char = chr(bindec($char));
        }

        return implode('', $chars);
    }
}

if (!function_exists('mutex')) {
    /** @return malkusch\lock\mutex\LockMutex */
    function mutex($name, $timeout = null)
    {
        if ($timeout) {
            $redis = Redis::connection()->client();
            $mutex = new malkusch\lock\mutex\PredisMutex([$redis], $name, $timeout);
        } else {
            $name = md5($name);
            $file = storage_path("app/$name.mutex");
            $mutex = new malkusch\lock\mutex\FlockMutex(fopen($file, 'w+'));
        }

        return $mutex;
    }
}

if (!function_exists('if_then')) {
    function if_then(...$args)
    {
        $argc = func_num_args();

        for ($i = 0; $i < $argc; $i = $i + 2) {
            if ($i == $argc - 1) {
                return $args[$i];
            }
            if ($args[$i]) {
                return $args[$i + 1];
            }
        }
    }
}

if (!function_exists('switch_case')) {
    function switch_case($value, ...$args)
    {
        $argc = func_num_args() - 1;

        for ($i = 0; $i < $argc; $i = $i + 2) {
            if ($i == $argc - 1) {
                return $args[$i];
            }
            if ($args[$i] == $value) {
                return $args[$i + 1];
            }
        }
    }
}

if (!function_exists('switch_case_strict')) {
    function switch_case_strict($value, ...$args)
    {
        $argc = func_num_args() - 1;

        for ($i = 0; $i < $argc; $i = $i + 2) {
            if ($i == $argc - 1) {
                return $args[$i];
            }
            if ($args[$i] === $value) {
                return $args[$i + 1];
            }
        }
    }
}

if (!function_exists('number_compare')) {
    function number_compare($number, $mt, $eq, $lt, $relative = 0)
    {
        if ($number > $relative) {
            return value($mt);
        } elseif ($number < $relative) {
            return value($lt);
        } else {
            return value($eq);
        }
    }
}

if (!function_exists('escape_like')) {
    function escape_like($string)
    {
        return str_replace(['%', '_'], ['\%', '\_'], $string);
    }
}

if (!function_exists('like_starts')) {
    function like_starts($string)
    {
        return escape_like($string).'%';
    }
}

if (!function_exists('like_ends')) {
    function like_ends($string)
    {
        return '%'.escape_like($string);
    }
}

if (!function_exists('like_contains')) {
    function like_contains($string)
    {
        return '%'.escape_like($string).'%';
    }
}

if (!function_exists('array_combine_values')) {
    function array_combine_values($array)
    {
        $array = array_values($array);
        $array = array_combine($array, $array);

        return $array;
    }
}

if (!function_exists('select_options')) {
    function select_options($options, $empty = false, $empty_text = '')
    {
        $options = array_combine_values($options);
        if ($empty) {
            $options = ['' => $empty_text] + $options;
        }

        return $options;
    }
}

if (!function_exists('array_init')) {
    function array_init(&$array, $key, $value = null)
    {
        if (array_get($array, $key) === null) {
            array_set($array, $key, value($value));
        }
    }
}

if (!function_exists('array_increment')) {
    function array_increment(&$array, $key, $value = 1)
    {
        $value = array_get($array, $key, 0) + $value;
        array_set($array, $key, $value);

        return $value;
    }
}

if (!function_exists('xml2array')) {
    function xml2array($contents, $get_attributes = 1)
    {
        if (!$contents) {
            return [];
        }

        if (!function_exists('xml_parser_create')) {
            //print "'xml_parser_create()' function not found!";
            return [];
        }
        //Get the XML parser of PHP - PHP must have this module for the parser to work
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $contents, $xml_values);
        xml_parser_free($parser);

        if (!$xml_values) {
            return;
        }//Hmm...

        //Initializations
        $xml_array = [];
        $parents = [];
        $opened_tags = [];
        $arr = [];

        $current = &$xml_array;

        //Go through the tags.
        foreach ($xml_values as $data) {
            unset($attributes, $value); //Remove existing values, or there will be trouble

            //This command will extract these variables into the foreach scope
            // tag(string), type(string), level(int), attributes(array).
            extract($data); //We could use the array by itself, but this cooler.

            $result = '';
            if ($get_attributes) {//The second argument of the function decides this.
                $result = [];
                if (isset($value)) {
                    $result['value'] = $value;
                }

                //Set the attributes too.
                if (isset($attributes)) {
                    foreach ($attributes as $attr => $val) {
                        if ($get_attributes == 1) {
                            $result['attr'][$attr] = $val;
                        } //Set all the attributes in a array called 'attr'
                    }
                }
            } elseif (isset($value)) {
                $result = $value;
            }

            //See tag status and do the needed.
            if ($type == 'open') {//The starting of the tag '<tag>'
                $parent[$level - 1] = &$current;

                if (!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                    $current[$tag] = $result;
                    $current = &$current[$tag];
                } else { //There was another element with the same tag name
                    if (isset($current[$tag][0])) {
                        array_push($current[$tag], $result);
                    } else {
                        $current[$tag] = [$current[$tag], $result];
                    }
                    $last = count($current[$tag]) - 1;
                    $current = &$current[$tag][$last];
                }
            } elseif ($type == 'complete') { //Tags that ends in 1 line '<tag />'
                //See if the key is already taken.
                if (!isset($current[$tag])) { //New Key
                    $current[$tag] = $result;
                } else { //If taken, put all things inside a list(array)
                    if ((is_array($current[$tag]) and $get_attributes == 0)//If it is already an array...
                        or (isset($current[$tag][0]) and is_array($current[$tag][0]) and $get_attributes == 1)) {
                        array_push($current[$tag], $result); // ...push the new element into that array.
                    } else { //If it is not an array...
                        $current[$tag] = [
                            $current[$tag],
                            $result,
                        ]; //...Make it an array using using the existing value and the new value
                    }
                }
            } elseif ($type == 'close') { //End of tag '</tag>'
                $current = &$parent[$level - 1];
            }
        }

        return $xml_array;
    }
}
