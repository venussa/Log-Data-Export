import sys, getopt, re, json

class LogConvert():

    fread = ""

    def readLogs(self, file):
        f = open(file, "r")
        self.fread = f.read()

    def convertToJsonLine(self, dst_output_file):
        if dst_output_file == "":
            dst_output_file = "result_convert.json"
        results_json = json.dumps(self.fread.splitlines())
        self.__saveLogs(dst_output_file, results_json)
        print("Success convert to json, output file: ", dst_output_file)
        
    
    def convertToJsonNginxErrorLog(self, dst_output_file):
        group_message = [
            "datetime",
            "errorType",
            "logMessage",
            "client",
            "server",
            "request",
            "host"
        ]

        if dst_output_file == "":
            dst_output_file = "result_convert_error_log_nginx.json"

        pattern = r'(?P<datetime>[\d+ :]+) \[(?P<errortype>.+)\] .*?: (?P<errormessage>.+), client: (?P<client>.+), server: (?P<server>.+), request: (?P<request>.+), host: (?P<host>.+)'
        matches = re.finditer(pattern, self.fread, re.MULTILINE)
        data_json = []
        for match_num, match in enumerate(matches, start=1):
            parse_json = {}
            for group_num in range(0, len(match.groups())):
                group_num = group_num + 1
                parse_json[group_message[group_num-1]] = match.group(group_num)
            data_json.append(parse_json)

        results_json = json.dumps(data_json)
        self.__saveLogs(dst_output_file, results_json)
        print("Success convert error log nginx to json, output file: ", dst_output_file)

    def convertToJsonNginxAccessLogs(self, dst_output_file):

        group_message = [
            "host",
            "datetime",
            "request",
            "status",
            "bandwidth",
            "referrer",
            "useragent",
        ]
        
        if dst_output_file == "":
            dst_output_file = "result_convert_access_log_nginx.json"

        regex = r'(?P<host>.+)\s-\s-\s\[(?P<datetime>.+)\]\s(?P<request>.+)\s\w+.+\s(?P<status>\d+)\s(?P<bandwidth>\d+)\s(?P<referrer>.+)\s(?P<useragent>.+)'
        
        matches = re.finditer(regex, self.fread, re.MULTILINE)
        data_json = []
        for match_num, match in enumerate(matches, start=1):
            parse_json = {}
            for group_num in range(0, len(match.groups())):
                group_num = group_num + 1
                parse_json[group_message[group_num-1]] = match.group(group_num)
            data_json.append(parse_json)
        results_json = json.dumps(data_json)
        self.__saveLogs(dst_output_file, results_json)
        print("Success convert access log nginx to json, output file: ", dst_output_file)

    
    def __saveLogs(self, file_path, content):
        fw = open(file_path, "w+")
        fw.write(content)
        fw.close()

    def convertToPlainText(self, dst_output_file):
        if dst_output_file == "":
            dst_output_file = "result_convert.txt"
        self.__saveLogs(dst_output_file, self.fread)
        print("Success convert to text, output file: ", dst_output_file)



def display_help():
    print(""" 
 Usege : python logtools.py <source file path> | [[-t <text/json|json_nginx>] | [-o <destination file path>]]

 t             file types available for conversion.
                - json_nginx_error    (Convert nginx log error parse byparamater)
                - json_nginx_access   (Convert nginx log access parse byparamater)
                - json         (Genereal logs convert parse by line
                - text         (Just convert log file to plaintext)

 o             Destination file output result.

 h             Display guid usage.

 Example : python logtools.py /var/log/ngin/access.log -t json_nginx_error -o mystorage/result.json.
    """)

def display_error(error):
    print(
        f"""Error: {error}

For help: python logtools.py -h 
"""
    )

def main(argv):
    try:
        opts, args = getopt.getopt(argv[2:],"o:t:")
        path_read_file_log = argv[1]
    except:
       return display_help()

    if path_read_file_log == "-h":
        return display_help()

    type_convert = "text"
    dst_output_file = ""
    for opt, arg in opts:
        if opt in ['-t']:
            if arg not in ["json", "text", "json_nginx_error", "json_nginx_access"]:
                display_error("Args -t invalid, only allow type json or text ")
                return
            type_convert = arg
        if opt in ['-o']:
            dst_output_file = arg
    try:
        logConvert = LogConvert()
        logConvert.readLogs(path_read_file_log)
        if type_convert == "json":
            logConvert.convertToJsonLine(dst_output_file)
        elif type_convert == "json_nginx_error":
            logConvert.convertToJsonNginxErrorLog(dst_output_file)
        elif type_convert == "json_nginx_access":
            logConvert.convertToJsonNginxAccessLogs(dst_output_file)
        else:
            logConvert.convertToPlainText(dst_output_file)
    except Exception as e:
        display_error(e)


if __name__ == "__main__":
   main(sys.argv)