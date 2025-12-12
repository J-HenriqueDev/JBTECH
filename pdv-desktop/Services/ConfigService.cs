using PdvDesktop.Models;

namespace PdvDesktop.Services
{
    public class ConfigService
    {
        private static IniService _iniService = new IniService();

        public static Config LoadConfig()
        {
            var config = new Config
            {
                ApiUrl = _iniService.ReadValue("API", "Url", ""),
                PrinterPort = _iniService.ReadValue("Impressora", "Porta", ""),
                PrinterType = _iniService.ReadValue("Impressora", "Tipo", "epson"),
                ScalePort = _iniService.ReadValue("Balança", "Porta", ""),
                ScaleBaudRate = int.TryParse(_iniService.ReadValue("Balança", "BaudRate", "9600"), out var baud) ? baud : 9600
            };

            return config;
        }

        public static void SaveConfig(Config config)
        {
            _iniService.WriteValue("API", "Url", config.ApiUrl);
            _iniService.WriteValue("Impressora", "Porta", config.PrinterPort);
            _iniService.WriteValue("Impressora", "Tipo", config.PrinterType);
            _iniService.WriteValue("Balança", "Porta", config.ScalePort);
            _iniService.WriteValue("Balança", "BaudRate", config.ScaleBaudRate.ToString());
        }

        public static string GetConfigPath()
        {
            return _iniService.GetIniPath();
        }

        public static bool ConfigExists()
        {
            return _iniService.FileExists();
        }

        public static bool IsApiUrlConfigured()
        {
            var config = LoadConfig();
            return !string.IsNullOrWhiteSpace(config.ApiUrl);
        }
    }
}
