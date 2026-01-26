<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoryKeywordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mapping = [
            'Placas-mãe' => 'placa mãe,motherboard,mainboard,lga,am4,am5,b450,b550,h61,h81,h310,h410,h510,z490,z590,z690,z790,x570',
            'Memórias' => 'memoria,ram,ddr3,ddr4,ddr5,4gb,8gb,16gb,32gb,udimm,sodimm,hyperx,fury,vengeance',
            'SSD' => 'ssd,nvme,m.2,sata,kingston,wd,sandisk,120gb,240gb,480gb,500gb,512gb,960gb,1tb,2tb,sn570,sn770,bx500,a400',
            'HD' => 'hd,disco rigido,hard disk,barracuda,seagate,western digital,wd blue,wd purple,surveillance,skyhawk',
            'Computador' => 'pc,computador,desktop,gamer,i3,i5,i7,i9,ryzen,office,home',
            'Outros' => 'cabo,adaptador,fonte,gabinete,cooler,fan,teclado,mouse,headset,fone,monitor,impressora,toner,cartucho'
        ];

        foreach ($mapping as $nome => $keywords) {
            $categoria = Categoria::where('nome', $nome)->first();
            if ($categoria) {
                $categoria->update(['palavras_chave' => $keywords]);
            }
        }
    }
}
