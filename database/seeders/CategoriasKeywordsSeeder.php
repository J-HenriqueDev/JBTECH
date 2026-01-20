<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriasKeywordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categorias = [
            [
                'nome' => 'Placas-mãe',
                'palavras_chave' => 'placa mae, motherboard, mainboard, lga, am4, am5, b450, b550, x570, z490, z590, h61, h81, h110, h310, h410, h510, h610, a320, a520'
            ],
            [
                'nome' => 'Memórias',
                'palavras_chave' => 'memoria ram, ddr3, ddr4, ddr5, sodimm, udimm, 4gb, 8gb, 16gb, 32gb, mhz, cl16, cl18, cl22, hyperx, corsair, adata'
            ],
            [
                'nome' => 'SSD',
                'palavras_chave' => 'ssd, nvme, m.2, sata, 120gb, 240gb, 480gb, 500gb, 512gb, 1tb, 2tb, kingston, wd, sandisk, crucial, solid state'
            ],
            [
                'nome' => 'HD',
                'palavras_chave' => 'hd, hdd, disco rigido, hard disk, seagate, western digital, toshiba, 500gb, 1tb, 2tb, 4tb, barracuda, purple, surveillance'
            ],
            [
                'nome' => 'Computador',
                'palavras_chave' => 'computador, pc, desktop, gabinete, cpu, gamer, office, home, servidor, workstation'
            ],
            [
                'nome' => 'Processadores',
                'palavras_chave' => 'processador, cpu, intel, amd, ryzen, core i3, core i5, core i7, core i9, athlon, pentium, celeron, threadripper, epyc, xeon'
            ],
            [
                'nome' => 'Placas de Vídeo',
                'palavras_chave' => 'placa de video, gpu, vga, geforce, rtx, gtx, radeon, rx, nvidia, graphics card, 1660, 3060, 4060, 3070, 4070, 3080, 4080'
            ],
            [
                'nome' => 'Fontes',
                'palavras_chave' => 'fonte atx, fonte sfx, fonte pc, 400w, 500w, 600w, 750w, 850w, 1000w, 1200w, 80 plus, bronze, gold, platinum, corsair, evga, msi, xpg, thermaltake, gigabyte, redragon'
            ],
            [
                'nome' => 'Carregadores e Fontes',
                'palavras_chave' => 'carregador, fonte notebook, fonte universal, carregador celular, carregador parede, carregador veicular, fonte 5v, fonte 9v, fonte 12v, fonte 24v, fonte 48v, fonte chaveada, fonte colmeia, fonte cftv, power bank, carregador portatil, magsafe, usb-c, lightning, tipo-c, fast charger, turbo power, baseus, ugreen'
            ],
            [
                'nome' => 'Periféricos',
                'palavras_chave' => 'teclado, mouse, headset, fone de ouvido, monitor, webcam, mousepad, gamer, mecanico, usb, sem fio, bluetooth, logitech, razer, redragon'
            ],
            [
                'nome' => 'Ferramentas e Insumos',
                'palavras_chave' => 'pasta termica, alcool isopropilico, limpa contato, ar comprimido, limpa tela, kit limpeza, estanho, solda, fluxo de solda, ferro de solda, estacao de solda, multimetro, alicate crimpador, alicate corte, chave phillips, chave fenda, kit chaves, manta antistatica, pulseira antistatica, organizador de cabos, abracadeira, enforca gato, fita isolante'
            ],
            [
                'nome' => 'Rede',
                'palavras_chave' => 'roteador, switch, placa de rede, wifi, wireless, adaptador usb, rj45, cabo de rede, patch cord, access point, tp-link, d-link, ubiquiti'
            ],
            [
                'nome' => 'Serviços',
                'palavras_chave' => 'servico, mao de obra, formatacao, instalacao, limpeza, reparo, manutencao, visita tecnica, consultoria, configuracao'
            ],
            [
                'nome' => 'Cabos e Adaptadores',
                'palavras_chave' => 'cabo, adaptador, hdmi, vga, displayport, dvi, usb-c, lightning, conversor, extensor, splitter'
            ],
            [
                'nome' => 'Automação Comercial',
                'palavras_chave' => 'balanca, impressora termica, leitor de codigo de barras, leitor qr, gaveta de dinheiro, sat fiscal, bobina termica, elgin, bematech, toledo'
            ],
            [
                'nome' => 'Energia',
                'palavras_chave' => 'nobreak, ups, estabilizador, filtro de linha, bateria selada, bateria 12v, ragtech, sms, apc, ts shara, protetor eletronico'
            ],
            [
                'nome' => 'Segurança Eletrônica',
                'palavras_chave' => 'camera, cftv, dvr, nvr, xvr, balun, conector bnc, video porteiro, camera ip, camera wi-fi, hikvision, intelbras, giga'
            ],
            [
                'nome' => 'Impressão e Suprimentos',
                'palavras_chave' => 'impressora, multifuncional, toner, cartucho, tinta, garrafa de tinta, papel a4, papel foto, papel termico, ribbon, fita matricial, fotocondutor, cilindro, refil, bulk ink, epson, hp, canon, brother, laserjet, ecotank, deskjet, ink tank'
            ],
            [
                'nome' => 'Áudio e Vídeo',
                'palavras_chave' => 'projetor, tela de projecao, suporte tv, soundbar, home theater, caixa de som, speaker, jbl, lg, samsung'
            ],
            [
                'nome' => 'Software',
                'palavras_chave' => 'windows, office, antivirus, licenca, serial, kaspersky, mcafee, microsoft, esd'
            ],
            [
                'nome' => 'Outros',
                'palavras_chave' => 'diversos, generico, acessorio, item'
            ]
        ];

        foreach ($categorias as $cat) {
            Categoria::updateOrCreate(
                ['nome' => $cat['nome']],
                ['palavras_chave' => $cat['palavras_chave']]
            );
        }
    }
}
