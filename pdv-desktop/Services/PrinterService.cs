using System;
using System.Collections.Generic;
using System.IO;
using System.IO.Ports;
using System.Net.Sockets;
using System.Text;
using System.Threading.Tasks;
using PdvDesktop.Models;

namespace PdvDesktop.Services
{
    public class PrinterService
    {
        private string _port = string.Empty;
        private string _type = string.Empty;

        public void Configure(string port, string type)
        {
            _port = port;
            _type = type;
        }

        public async Task<bool> PrintCupomAsync(Venda venda)
        {
            try
            {
                var commands = BuildCupomCommands(venda);
                
                if (_port.StartsWith("COM") || _port.StartsWith("com"))
                {
                    return await PrintSerialAsync(commands);
                }
                else if (_port.StartsWith("USB") || _port.StartsWith("usb"))
                {
                    return await PrintUsbAsync(commands);
                }
                else if (_port.Contains(":") || _port.Contains("."))
                {
                    return await PrintNetworkAsync(commands);
                }

                return false;
            }
            catch (Exception ex)
            {
                throw new Exception($"Erro ao imprimir: {ex.Message}");
            }
        }

        private byte[] BuildCupomCommands(Venda venda)
        {
            var commands = new List<byte>();

            // Inicializa impressora (ESC @)
            commands.AddRange(new byte[] { 0x1B, 0x40 });

            // Cabeçalho centralizado
            commands.Add(0x1B); // ESC
            commands.Add(0x61); // a (alinhamento)
            commands.Add(0x01); // 1 = centro

            // Texto do cabeçalho
            var header = Encoding.UTF8.GetBytes("LOJA EXEMPLO\nCNPJ: 00.000.000/0001-00\n");
            commands.AddRange(header);

            // Linha
            commands.AddRange(new byte[] { 0x1B, 0x61, 0x00 }); // Alinhamento à esquerda
            commands.AddRange(Encoding.UTF8.GetBytes("--------------------------------\n"));

            // Informações da venda
            commands.AddRange(Encoding.UTF8.GetBytes($"CUPOM: {venda.NumeroCupom}\n"));
            commands.AddRange(Encoding.UTF8.GetBytes($"DATA: {venda.DataVenda:dd/MM/yyyy HH:mm}\n"));
            commands.AddRange(Encoding.UTF8.GetBytes("--------------------------------\n"));

            // Itens
            foreach (var item in venda.Produtos)
            {
                var linha = $"{item.Nome.PadRight(20).Substring(0, Math.Min(20, item.Nome.Length))} " +
                           $"{item.Quantidade}x R$ {item.ValorUnitario:F2} = R$ {item.ValorTotal:F2}\n";
                commands.AddRange(Encoding.UTF8.GetBytes(linha));
            }

            // Total
            commands.AddRange(Encoding.UTF8.GetBytes("--------------------------------\n"));
            commands.Add(0x1B); commands.Add(0x61); commands.Add(0x02); // Alinhamento à direita
            commands.AddRange(Encoding.UTF8.GetBytes($"TOTAL: R$ {venda.ValorTotal:F2}\n"));

            // Rodapé
            commands.AddRange(new byte[] { 0x1B, 0x61, 0x01 }); // Centro
            commands.AddRange(Encoding.UTF8.GetBytes("OBRIGADO PELA PREFERENCIA!\n\n\n"));

            // Corta papel
            commands.AddRange(new byte[] { 0x1D, 0x56, 0x41, 0x03 }); // Corta papel

            return commands.ToArray();
        }

        private async Task<bool> PrintSerialAsync(byte[] commands)
        {
            return await Task.Run(() =>
            {
                try
                {
                    using (var port = new SerialPort(_port, 9600, Parity.None, 8, StopBits.One))
                    {
                        port.Open();
                        port.Write(commands, 0, commands.Length);
                        port.Close();
                        return true;
                    }
                }
                catch
                {
                    return false;
                }
            });
        }

        private async Task<bool> PrintUsbAsync(byte[] commands)
        {
            // Para USB, geralmente é via RawPrint ou similar
            // Implementação específica depende da biblioteca USB
            return await Task.FromResult(false);
        }

        private async Task<bool> PrintNetworkAsync(byte[] commands)
        {
            return await Task.Run(() =>
            {
                try
                {
                    var parts = _port.Split(':');
                    var host = parts[0];
                    var port = parts.Length > 1 ? int.Parse(parts[1]) : 9100;

                    using (var client = new TcpClient())
                    {
                        client.Connect(host, port);
                        using (var stream = client.GetStream())
                        {
                            stream.Write(commands, 0, commands.Length);
                            stream.Flush();
                        }
                    }
                    return true;
                }
                catch
                {
                    return false;
                }
            });
        }

        public async Task<bool> TestPrinterAsync()
        {
            try
            {
                var testVenda = new Venda 
                { 
                    NumeroCupom = "TESTE", 
                    DataVenda = DateTime.Now,
                    ValorTotal = 0,
                    Produtos = new List<ItemVenda>()
                };
                return await PrintCupomAsync(testVenda);
            }
            catch
            {
                return false;
            }
        }
    }
}
