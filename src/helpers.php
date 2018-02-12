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
    function upload_limit()
    {
        return min([
            (int) ini_get('upload_max_filesize'),
            (int) ini_get('post_max_size'),
            (int) ini_get('memory_limit'),
        ]);
    }
}

if (!function_exists('replace_newlines')) {
    function replace_newlines($string, $symbol = "\n")
    {
        return str_replace(["\r\n", "\r", "\n"], $symbol, $string);
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

if (!function_exists('asset_manifest')) {
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

if (!function_exists('email_normalize')) {
    function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
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
    function number($number, $decimals = 0, $units = null, $separator = ' ')
    {
        $number = number_format($number, $decimals, ',', $separator);
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
            $number .= $separator.$units;
        }

        return $number;
    }
}

if (!function_exists('filesize_format')) {
    function filesize_format($size, $locale = null)
    {
        $units = [
            'en' => ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
            'ru' => ['Б', 'КБ', 'МБ', 'ГБ', 'ТБ', 'ПБ', 'ЭБ', 'ЗБ', 'ИБ'],
        ];
        $units = $units[$locale ?: locale()] ?: $units['en'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        $number = $size / pow(1024, $power);

        return number($number, 1, $units[$power]);
    }
}

if (!function_exists('name_initials')) {
    function name_initials($fullname)
    {
        $parts = explode(' ', $fullname);
        $result = [];
        $result[] = array_shift($parts);
        foreach ($parts as $part) {
            $result[] = mb_substr($part, 0, 1).'.';
        }

        return implode(' ', $result);
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
