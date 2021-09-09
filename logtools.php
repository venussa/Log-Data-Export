<?php

class logtools
{
	protected $argv;
	protected $file_format;

	public function __construct($argument)
	{
		$this->argv = $argument;

		$this->preparation();
	}

	protected function build_paramater()
	{

		$config = ["-t" => "file_format", "-o" => "destination", "-h" => "help"];

		$capture = [];

		if(count($this->argv) == 1)
		{
			echo $this->guide_usage();
			exit;
		}

		foreach ($this->argv as $key => $value)
		{
			if (in_array($value, array_keys($config)))
			{
				$capture[$config[$value]] = (isset($this->argv[($key + 1)])) ? $this->argv[($key + 1)] : "";
			}
			else if ($key == 1)
			{
				$capture["sources"] = $value;
			}
		}

		return json_decode(json_encode($capture));
	}

	protected function check_destination($data)
	{
		if (isset($data->destination))
		{
			if (is_dir($data->destination))
			{
				$copy_filename = explode("/", $data->sources);
				$copy_filename = end($copy_filename);
				$data->destination = rtrim($data->destination, "/")."/".$copy_filename.".".$data->file_format;
			}
			else
			{
				$parse_dir = explode("/", $data->destination);
				
				if (count($parse_dir) > 1)
				{
					unset($parse_dir[count($parse_dir)-1]);
					$parse_dir = implode("/", $parse_dir);
					
					if (!file_exists($parse_dir))
					{
						echo "\n ERROR : Destination path not found.\n\n Tools Guide\n".$this->guide_usage();
						exit;
					}
				}
				else
				{
					$data->destination = $data->destination;
				}
			}
		}
		else
		{
			$filename = explode("/", $data->sources);
			$data->destination = end($filename).".".$data->file_format;
		}

		return $data->destination;
	}

	protected function check_file_format($data)
	{
		$file_format = [
			"text" 				=> "txt", 
			"json" 				=> "json",
			"json_nginx_error" 	=> "json",
			"json_nginx_access" => "json"
		];

		if (!isset($data->file_format)) return $file_format["text"];

		if (isset($data->file_format))
		{
			if (!in_array($data->file_format, array_keys($file_format))) 
			{
				echo "\n ERROR : Invalid file format.\n\n Tools Guide\n".$this->guide_usage();
				exit;
			}
			else
			{
				$this->file_format = strtolower($data->file_format);
				$data->file_format = $file_format[$data->file_format];
			}
		}
		else
		{
			$data->file_format = $file_format["text"];
		}

		return $data->file_format;
	}

	protected function guide_usage()
	{
		$text  = "\n Usege : php logtools.php <source file path> | [[-t <text/json|json_nginx>] | [-o <destination file path>]]\n\n";

		$text .= " t             file types available for conversion.\n";
		$text .= "               - json_nginx_error    (Convert nginx log error parse byparamater)\n";
		$text .= "               - json_nginx_access   (Convert nginx log access parse byparamater)\n";
		$text .= "               - json                (Genereal logs convert parse by line)\n";
		$text .= "               - text                (Just convert log file to plaintext)\n\n";
		$text .= " o             Destination file output result.\n\n";
		$text .= " h             Display guid usage.\n\n";
		$text .= " Example : php logtools.php /var/log/nginx/access.log -t json_nginx -o mystorage/result.json\n";
		
		return $text;
	}

	protected function export($data)
	{
		$open = implode(null, file($data->sources));
		$split_line = explode("\n", $open);

		if ($this->file_format == "json")
		{
			return $split_line;
		}

		if ($this->file_format == "json_nginx_error") $regex = '/(?P<datetime>[\d+ :]+) \[(?P<errortype>.+)\] .*?: (?P<errormessage>.+), client: (?P<client>.+), server: (?P<server>.+), request: (?P<request>.+), host: (?P<host>.+)/';
		if ($this->file_format == "json_nginx_access") $regex = '/(?P<host>.+)\s-\s-\s\[(?P<datetime>.+)\]\s"(?P<request>.+)\s\w+.+"\s(?P<status>\d+)\s(?P<bandwidth>\d+)\s"(?P<referrer>.+)"\s"(?P<useragent>.+)"/';

		preg_match_all($regex, $open, $result);

		foreach ($result as $key => $value)
		{
			if (!is_numeric($key))
			{
				$max_loop = count($value);

				for ($i = 0; $i < $max_loop;  $i++)
				{
					$build[$i][$key] = $value[$i];
				}
			}
		}
		
		if (isset($build))	return $build;

		return $split_line;
	}

	protected function preparation()
	{
		$data = $this->build_paramater();

		if (isset($data->help))
		{
			echo $this->guide_usage();
			exit;
		}

		if (isset($data->sources))
			if (!file_exists($data->sources))
			{
				echo "\n ERROR : Source file not found. \n\n Tools Guide\n".$this->guide_usage();
				exit;
			}

		$data->file_format = $this->check_file_format($data);

		$data->destination = $this->check_destination($data);

		$open_data = implode(null, file($data->sources, FILE_SKIP_EMPTY_LINES));
		
		if (strpos(" ".$this->file_format, "json"))
			$log_data = json_encode($this->export($data, $this->file_format));

		else
			$log_data = $open_data;

		file_put_contents($data->destination, $log_data);

		echo "\nSuccess convert log to ".$data->file_format.", Output file : ".$data->destination."\n";
	}

}

(new logtools($argv));