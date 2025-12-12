using System.Windows;
using System.Windows.Input;

namespace PdvDesktop.Views
{
    public partial class InputDialog : Window
    {
        public string ResponseText { get; set; }

        public InputDialog(string title, string prompt, string defaultValue = "")
        {
            InitializeComponent();
            Title = title;
            DataContext = this;
            ResponseText = defaultValue;
            txtInput.Focus();
            txtInput.SelectAll();
        }

        private void BtnOk_Click(object sender, RoutedEventArgs e)
        {
            DialogResult = true;
        }

        private void TxtInput_KeyDown(object sender, KeyEventArgs e)
        {
            if (e.Key == Key.Enter)
            {
                DialogResult = true;
            }
        }
    }
}


