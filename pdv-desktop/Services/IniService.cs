using System;
using System.IO;
using System.Reflection;
using System.Runtime.InteropServices;

namespace PdvDesktop.Services
{
    public class IniService
    {
        private string _iniPath;

        public IniService()
        {
            // Pega a pasta do executável
            var exePath = Assembly.GetExecutingAssembly().Location;
            var exeDir = Path.GetDirectoryName(exePath);
            _iniPath = Path.Combine(exeDir ?? "", "config.ini");
        }

        [DllImport("kernel32.dll", CharSet = CharSet.Unicode)]
        private static extern int GetPrivateProfileString(
            string section,
            string key,
            string defaultValue,
            System.Text.StringBuilder result,
            int size,
            string filePath);

        [DllImport("kernel32.dll", CharSet = CharSet.Unicode)]
        private static extern bool WritePrivateProfileString(
            string section,
            string key,
            string value,
            string filePath);

        public string ReadValue(string section, string key, string defaultValue = "")
        {
            var result = new System.Text.StringBuilder(255);
            GetPrivateProfileString(section, key, defaultValue, result, 255, _iniPath);
            return result.ToString();
        }

        public void WriteValue(string section, string key, string value)
        {
            WritePrivateProfileString(section, key, value, _iniPath);
        }

        public bool FileExists()
        {
            return File.Exists(_iniPath);
        }

        public string GetIniPath()
        {
            return _iniPath;
        }
    }
}


