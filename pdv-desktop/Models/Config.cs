using System;

namespace PdvDesktop.Models
{
    public class Config
    {
        public string ApiUrl { get; set; } = string.Empty;
        public string PrinterPort { get; set; } = string.Empty;
        public string PrinterType { get; set; } = "epson";
        public string ScalePort { get; set; } = string.Empty;
        public int ScaleBaudRate { get; set; } = 9600;
    }
}


