using System.ComponentModel;

namespace PdvDesktop.ViewModels
{
    public class LoginViewModel : INotifyPropertyChanged
    {
        private string _operador = string.Empty;

        public string Operador
        {
            get => _operador;
            set
            {
                _operador = value;
                OnPropertyChanged(nameof(Operador));
            }
        }

        public event PropertyChangedEventHandler? PropertyChanged;

        protected void OnPropertyChanged(string propertyName)
        {
            PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
        }
    }
}
