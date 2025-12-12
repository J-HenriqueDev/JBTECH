using System;
using System.IO.Ports;
using System.Threading.Tasks;

namespace PdvDesktop.Services
{
    public class ScaleService
    {
        private SerialPort? _serialPort;
        private string _port = string.Empty;
        private int _baudRate;

        public void Configure(string port, int baudRate)
        {
            _port = port;
            _baudRate = baudRate;
        }

        public async Task<decimal?> ReadWeightAsync()
        {
            return await Task.Run(() =>
            {
                try
                {
                    if (string.IsNullOrEmpty(_port))
                    {
                        return null;
                    }

                    using (_serialPort = new SerialPort(_port, _baudRate, Parity.None, 8, StopBits.One))
                    {
                        _serialPort.ReadTimeout = 2000;
                        _serialPort.Open();

                        // Lê dados da balança
                        var data = _serialPort.ReadLine();
                        _serialPort.Close();

                        // Processa o peso (formato varia conforme a balança)
                        return ParseWeight(data);
                    }
                }
                catch
                {
                    return null;
                }
            });
        }

        private decimal? ParseWeight(string data)
        {
            try
            {
                // Remove caracteres não numéricos exceto ponto e vírgula
                var cleaned = System.Text.RegularExpressions.Regex.Replace(data, @"[^\d.,]", "");
                cleaned = cleaned.Replace(",", ".");

                if (decimal.TryParse(cleaned, out var weight))
                {
                    return weight > 0 ? weight : null;
                }

                return null;
            }
            catch
            {
                return null;
            }
        }

        public string[] GetAvailablePorts()
        {
            return SerialPort.GetPortNames();
        }

        public async Task<bool> TestScaleAsync()
        {
            try
            {
                var weight = await ReadWeightAsync();
                return weight.HasValue;
            }
            catch
            {
                return false;
            }
        }
    }
}
