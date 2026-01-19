<?php

namespace App\Services;

use NFePHP\DA\NFe\Danfe;

class CustomDanfe extends Danfe
{
  protected function calculoEspacoVericalDadosAdicionais()
  {
    $this->textoAdic = '';
    $fontProduto = [
      'font'  => $this->fontePadrao,
      'size'  => 8,
      'style' => ''
    ];
    $k = $this->pdf->k;
    $this->textadicfontsize = $fontProduto['size'] / $k;
    $this->textoAdic .= $this->geraInformacoesDasNotasReferenciadas();
    if (isset($this->infAdic)) {
      $i = 0;
      if ($this->textoAdic != '') {
        $this->textoAdic .= ". \n";
      }
      $this->textoAdic .= !empty($this->getTagValue($this->infAdic, "infCpl"))
        ? 'Inf. Contribuinte: ' . $this->getTagValue($this->infAdic, "infCpl")
        : '';
      $infPedido = $this->geraInformacoesDaTagCompra();
      if ($infPedido != "") {
        $this->textoAdic .= $infPedido;
      }
      if ($this->exibirEmailDestinatario) {
        $this->textoAdic .= $this->getTagValue($this->dest, "email", ' Email do Destinatário: ');
      }
      $this->textoAdic .= !empty($this->getTagValue($this->infAdic, "infAdFisco"))
        ? "\n Inf. fisco: " . $this->getTagValue($this->infAdic, "infAdFisco")
        : '';
      if ($this->obsshow) {
        $obsCont = $this->infAdic->getElementsByTagName("obsCont");
        if (isset($obsCont)) {
          foreach ($obsCont as $obs) {
            $campo = $obsCont->item($i)->getAttribute("xCampo");
            $xTexto = !empty($obsCont->item($i)->getElementsByTagName("xTexto")->item(0)->nodeValue)
              ? $obsCont->item($i)->getElementsByTagName("xTexto")->item(0)->nodeValue
              : '';
            $this->textoAdic .= "\n" . $campo . ':  ' . trim($xTexto);
            $i++;
          }
        }
      }
    }
    if ($this->exibirValorTributos) {
      $flagVTT = strpos(strtolower(trim($this->textoAdic)), 'valor');
      $flagVTT = $flagVTT || strpos(strtolower(trim($this->textoAdic)), 'vl');
      $flagVTT = $flagVTT && strpos(strtolower(trim($this->textoAdic)), 'aprox');
      $flagVTT = $flagVTT && (strpos(strtolower(trim($this->textoAdic)), 'trib') ||
        strpos(strtolower(trim($this->textoAdic)), 'imp'));
      $vTotTrib = $this->getTagValue($this->ICMSTot, 'vTotTrib');
      if ($vTotTrib != '' && !$flagVTT) {
        $this->textoAdic .= "\n Valor Aproximado dos Tributos : R$ "
          . number_format($vTotTrib, 2, ",", ".");
      }
    }
    $this->textoAdic = str_replace(";", "\n", $this->textoAdic);
    $numlinhasdados = $this->pdf->getNumLines($this->textoAdic, $this->wAdic, $fontProduto) + 2;
    $this->textadicfontsize = $this->pdf->fontSize;
    $hdadosadic = ceil($numlinhasdados * ($this->textadicfontsize));
    if ($hdadosadic > 140) {
      for ($f = 8; $f > 3; $f--) {
        $this->pdf->setFont($this->fontePadrao, '', $f);
        $fontProduto = [
          'font'  => $this->fontePadrao,
          'size'  => $f,
          'style' => ''
        ];
        $numlinhasdados = $this->pdf->getNumLines($this->textoAdic, $this->wAdic, $fontProduto) + 3;
        $this->textadicfontsize = $this->pdf->fontSize;
        $hdadosadic = ceil($numlinhasdados * $this->textadicfontsize);
        if ($hdadosadic <= 190) {
          $hdadosadic = ceil($hdadosadic);
          break;
        }
      }
    }
    if ($hdadosadic < 22) {
      $hdadosadic = 22;
    }
    return $hdadosadic + 5; // Aumenta buffer para distanciar mais os produtos
  }

  protected function dadosAdicionais($x, $y, $h)
  {
    $h -= 5; // Remove o buffer maior para manter o gap visual
    $y = $this->maxH - (7 + $h);
    $texto = "DADOS ADICIONAIS";
    if ($this->orientacao == 'P') {
      $w = $this->wPrint;
    } else {
      $w = $this->wPrint - $this->wCanhoto;
    }
    $aFont = ['font' => $this->fontePadrao, 'size' => 7, 'style' => 'B'];
    $this->pdf->textBox($x, $y, $w, 8, $texto, $aFont, 'T', 'L', 0, '');

    $texto = "INFORMAÇÕES COMPLEMENTARES";
    $y += 3;
    $w = $this->wAdic;
    $aFont = ['font' => $this->fontePadrao, 'size' => 6, 'style' => 'B'];

    // Desenha borda
    $this->pdf->textBox($x, $y, $w, $h, '', $aFont, 'T', 'L', 1, '');
    // Desenha texto com offset para evitar sobreposição
    $this->pdf->textBox($x + 1, $y + 1, $w - 2, $h - 2, $texto, $aFont, 'T', 'L', 0, '');

    $y += 1;
    $aFont = ['font' => $this->fontePadrao, 'size' => $this->textadicfontsize * $this->pdf->k, 'style' => ''];
    $this->pdf->textBox($x, $y + 2, $w - 2, $h, $this->textoAdic, $aFont, 'T', 'L', 0, '', false);

    $texto = "RESERVADO AO FISCO";
    if (isset($this->nfeProc) && $this->nfeProc->getElementsByTagName("xMsg")->length) {
      $texto = $texto . ' ' . $this->nfeProc->getElementsByTagName("xMsg")->item(0)->nodeValue;
    }
    $x += $w;
    $y -= 1;
    if ($this->orientacao === 'P') {
      $w = $this->wPrint - $w;
    } else {
      $w = $this->wPrint - $w - $this->wCanhoto;
    }
    $aFont = ['font' => $this->fontePadrao, 'size' => 6, 'style' => 'B'];

    // Separa borda e texto para o Fisco também
    $this->pdf->textBox($x, $y, $w, $h, '', $aFont, 'T', 'L', 1, '');
    $this->pdf->textBox($x + 1, $y + 1, $w - 2, $h - 2, $texto, $aFont, 'T', 'L', 0, '');

    $xJust = $this->getTagValue($this->ide, 'xJust', 'Justificativa: ');
    $dhCont = $this->getTagValue($this->ide, 'dhCont', ' Entrada em contingência : ');
    $texto = '';
    switch ($this->tpEmis) {
      case 4:
        $texto = "CONTINGÊNCIA EPEC\n" . $dhCont . "\n" . $xJust;
        break;
      case 5:
        $texto = "CONTINGÊNCIA FSDA\n" . $dhCont . "\n" . $xJust;
        break;
      case 6:
        $texto = "CONTINGÊNCIA SVC-AN\n" . $dhCont . "\n" . $xJust;
        break;
      case 7:
        $texto = "CONTINGÊNCIA SVC-RS\n" . $dhCont . "\n" . $xJust;
        break;
    }
    $y += 2;
    $aFont = ['font' => $this->fontePadrao, 'size' => 7, 'style' => ''];
    $this->pdf->textBox($x, $y, $w - 2, $h, $texto, $aFont, 'T', 'L', 0, '', false);

    return $y + $h;
  }
}
