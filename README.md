# Log-Data-Export

Usege : php logtools.php <source file path> | [[-t <text/json|json_nginx>] | [-o <destination file path>]][/code]

1. [t] => file types available for conversion.
- json_nginx_error (Convert nginx log error parse byparamater)
- json_nginx_access (Convert nginx log access parse byparamater)
- json (Genereal logs convert parse by line
- text (Just convert log file to plaintext)
2. [o] => Destination file output result.
3. [h] => Display guid usage.

Example : python logtools.py /var/log/ngin/access.log -t json_nginx_error -o mystorage/result.json.
