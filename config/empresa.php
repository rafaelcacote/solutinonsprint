<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dados exibidos no PDF de orçamento (bloco "De / Emitente")
    |--------------------------------------------------------------------------
    */

    'nome' => env('EMPRESA_NOME', env('APP_NAME', 'Empresa')),

    'endereco' => env('EMPRESA_ENDERECO', ''),

    'telefone' => env('EMPRESA_TELEFONE', ''),

    'email' => env('EMPRESA_EMAIL', ''),

    /** Caminho relativo a public/ (PNG/JPEG recomendados para o DomPDF) */
    'logo_pdf' => env('EMPRESA_LOGO_PDF', 'images/logo/logo_print.png'),

    /**
     * Largura da logo no PDF (px). Raster (PNG/JPEG) usa data URI; SVG é embutido inline.
     */
    'logo_pdf_width_px' => (int) env('EMPRESA_LOGO_PDF_WIDTH', 200),

];
