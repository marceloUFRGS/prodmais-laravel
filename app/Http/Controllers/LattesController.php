<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\Work;
use Illuminate\Support\Facades\DB;

class LattesController extends Controller
{

    function lattesID10($lattesID16)
    {
        $url = 'https://lattes.cnpq.br/' . $lattesID16 . '';
        $headers = @get_headers($url);
        $lattesID10 = "";
        foreach ($headers as $h) {
            if (substr($h, 0, 87) == 'Location: http://buscatextual.cnpq.br/buscatextual/visualizacv.do?metodo=apresentar&id=') {
                $lattesID10 = trim(substr($h, 87));
                break;
            }
        }
        return $lattesID10;
    }

    public function processaPalavrasChaveLattes($palavras_chave)
    {
        $array_result = [];
        foreach (range(1, 6) as $number) {
            if (!empty($palavras_chave['@attributes']["PALAVRA-CHAVE-$number"])) {
                $array_result[] = $palavras_chave['@attributes']["PALAVRA-CHAVE-$number"];
            }
        }
        return $array_result;
    }

    public function processaURL($url)
    {
        $url_array = explode('[', $url);
        if (isset($url_array[1])) {
            $url_response = str_replace(']', '', $url_array[1]);
            return $url_response;
        } else {
            return "";
        }
    }

    public function artigos(array $artigos, array $attributes)
    {
        //echo "<pre>" . print_r($attributes, true) . "</pre>";
        //echo "<pre>" . print_r($artigos, true) . "</pre>";
        foreach ($artigos['ARTIGO-PUBLICADO'] as $artigo) {
            $work = new Work;
            $work->fill([
                'datePublished' => $artigo['DADOS-BASICOS-DO-ARTIGO']['@attributes']['ANO-DO-ARTIGO'],
                'doi' => $artigo['DADOS-BASICOS-DO-ARTIGO']['@attributes']['DOI'],
                'inLanguage' => $artigo['DADOS-BASICOS-DO-ARTIGO']['@attributes']['IDIOMA'],
                'isPartOf' => $artigo['DETALHAMENTO-DO-ARTIGO']['@attributes']['TITULO-DO-PERIODICO-OU-REVISTA'],
                'issn' => $artigo['DETALHAMENTO-DO-ARTIGO']['@attributes']['ISSN'],
                'issueNumber' => $artigo['DETALHAMENTO-DO-ARTIGO']['@attributes']['SERIE'],
                'name' => $artigo['DADOS-BASICOS-DO-ARTIGO']['@attributes']['TITULO-DO-ARTIGO'],
                'pageEnd' => $artigo['DETALHAMENTO-DO-ARTIGO']['@attributes']['PAGINA-FINAL'],
                'pageStart' => $artigo['DETALHAMENTO-DO-ARTIGO']['@attributes']['PAGINA-INICIAL'],
                'type' => 'Artigo publicado',
                'url' => $artigo['DADOS-BASICOS-DO-ARTIGO']['@attributes']['HOME-PAGE-DO-TRABALHO'],
                'volumeNumber' => $artigo['DETALHAMENTO-DO-ARTIGO']['@attributes']['VOLUME'],

                //'about' => $artigo['PALAVRAS-CHAVE'],
            ]);

            if (isset($artigo['AUTORES'])) {
                foreach ($artigo['AUTORES'] as $autores) {
                    if (isset($autores['@attributes'])) {
                        $aut_array[] = $autores['@attributes'];
                        $aut_name_array[] = $autores['@attributes']['NOME-COMPLETO-DO-AUTOR'];
                    } else {
                        $aut_array[] = $autores;
                        $aut_name_array[] = $autores['NOME-COMPLETO-DO-AUTOR'];
                    }
                }
                $work->fill([
                    'author' => $aut_array,
                    'author_array' => $aut_name_array,
                ]);
                unset($aut_array);
                unset($aut_name_array);
            }

            if (isset($artigo['PALAVRAS-CHAVE'])) {
                $about_array = $this->processaPalavrasChaveLattes($artigo['PALAVRAS-CHAVE']);
                $work->fill([
                    'about' => $about_array,
                ]);
            }

            if (isset($artigo['DADOS-BASICOS-DO-ARTIGO']['@attributes']['HOME-PAGE-DO-TRABALHO'])) {
                $url = $this->processaURL($artigo['DADOS-BASICOS-DO-ARTIGO']['@attributes']['HOME-PAGE-DO-TRABALHO']);
                $work->fill([
                    'url' => $url,
                ]);
            }

            try {
                $work->save();
                unset($array_result_pc);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    public function trabalhosEmEventos(array $trabalhosEmEventos, array $attributes)
    {
        //echo "<pre>" . print_r($attributes, true) . "</pre>";
        //echo "<pre>" . print_r($trabalhoEmEventoss, true) . "</pre>";
        foreach ($trabalhosEmEventos['TRABALHO-EM-EVENTOS'] as $trabalhoEmEventos) {
            $work = new Work;
            $work->fill([
                'datePublished' => $trabalhoEmEventos['DADOS-BASICOS-DO-TRABALHO']['@attributes']['ANO-DO-TRABALHO'],
                'doi' => $trabalhoEmEventos['DADOS-BASICOS-DO-TRABALHO']['@attributes']['DOI'],
                'educationEvent' => $trabalhoEmEventos['DETALHAMENTO-DO-TRABALHO']['@attributes']["NOME-DO-EVENTO"],
                'inLanguage' => $trabalhoEmEventos['DADOS-BASICOS-DO-TRABALHO']['@attributes']['IDIOMA'],
                'isPartOf' => $trabalhoEmEventos['DETALHAMENTO-DO-TRABALHO']['@attributes']['TITULO-DOS-ANAIS-OU-PROCEEDINGS'],
                'name' => $trabalhoEmEventos['DADOS-BASICOS-DO-TRABALHO']['@attributes']['TITULO-DO-TRABALHO'],
                'pageEnd' => $trabalhoEmEventos['DETALHAMENTO-DO-TRABALHO']['@attributes']['PAGINA-FINAL'],
                'pageStart' => $trabalhoEmEventos['DETALHAMENTO-DO-TRABALHO']['@attributes']['PAGINA-INICIAL'],
                'type' => 'Trabalhos em eventos',
                'url' => $trabalhoEmEventos['DADOS-BASICOS-DO-TRABALHO']['@attributes']['HOME-PAGE-DO-TRABALHO'],

                //'about' => $trabalhoEmEventos['PALAVRAS-CHAVE'],
            ]);

            if (isset($trabalhoEmEventos['AUTORES'])) {
                foreach ($trabalhoEmEventos['AUTORES'] as $autores) {
                    if (isset($autores['@attributes'])) {
                        $aut_array[] = $autores['@attributes'];
                        $aut_name_array[] = $autores['@attributes']['NOME-COMPLETO-DO-AUTOR'];
                    } else {
                        $aut_array[] = $autores;
                        $aut_name_array[] = $autores['NOME-COMPLETO-DO-AUTOR'];
                    }
                }
                $work->fill([
                    'author' => $aut_array,
                    'author_array' => $aut_name_array,
                ]);
                unset($aut_array);
                unset($aut_name_array);
            }

            if (isset($trabalhoEmEventos['PALAVRAS-CHAVE'])) {
                $about_array = $this->processaPalavrasChaveLattes($trabalhoEmEventos['PALAVRAS-CHAVE']);
                $work->fill([
                    'about' => $about_array,
                ]);
            }

            if (isset($trabalhoEmEventos['DADOS-BASICOS-DO-TRABALHO']['@attributes']['HOME-PAGE-DO-TRABALHO'])) {
                $url = $this->processaURL($trabalhoEmEventos['DADOS-BASICOS-DO-TRABALHO']['@attributes']['HOME-PAGE-DO-TRABALHO']);
                $work->fill([
                    'url' => $url,
                ]);
            }

            try {
                $work->save();
                unset($array_result_pc);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    public function createPerson(array $curriculo, array $dados_complementares, array $outra_producao, array $attributes)
    {
        //echo "<pre>" . print_r($attributes, true) . "</pre>";
        //echo "<pre>" . print_r($curriculo, true) . "</pre>";
        //echo "<pre>" . print_r($dados_complementares, true) . "</pre>";
        //echo "<pre>" . print_r($outra_producao, true) . "</pre>";

        $person = new Person();

        $person->fill([
            'id' => $attributes['NUMERO-IDENTIFICADOR'],
            'lattesDataAtualizacao' => $attributes['DATA-ATUALIZACAO'],
            'resumoCVpt' => $curriculo['RESUMO-CV']['@attributes']['TEXTO-RESUMO-CV-RH'],
            'resumoCVen' => $curriculo['RESUMO-CV']['@attributes']['TEXTO-RESUMO-CV-RH-EN'],
            'name' => $curriculo['@attributes']['NOME-COMPLETO'],
            'nacionalidade' => $curriculo['@attributes']['PAIS-DE-NACIONALIDADE'],
            'nomeCitacoesBibliograficas' => $curriculo['@attributes']['NOME-EM-CITACOES-BIBLIOGRAFICAS'],
            'orcid' => $curriculo['@attributes']['ORCID-ID'],
            'idiomas' => $curriculo['IDIOMAS'],
            'formacao' => $curriculo['FORMACAO-ACADEMICA-TITULACAO']
        ]);

        $lattesID10 = $this->lattesID10($attributes['NUMERO-IDENTIFICADOR']);

        if (!empty($lattesID10)) {
            $person->fill([
                'lattesID10' => $lattesID10
            ]);
        }

        if (isset($curriculo['ATUACOES-PROFISSIONAIS'])) {
            $person->fill([
                'atuacao' => $curriculo['ATUACOES-PROFISSIONAIS']
            ]);
        }
        if (isset($dados_complementares['ORIENTACOES-EM-ANDAMENTO'])) {
            $person->fill([
                'orientacoesEmAndamento' => $dados_complementares['ORIENTACOES-EM-ANDAMENTO']
            ]);
        }
        if (isset($outra_producao['ORIENTACOES-CONCLUIDAS'])) {
            $person->fill([
                'orientacoesConcluidas' => $outra_producao['ORIENTACOES-CONCLUIDAS']
            ]);
        }


        try {
            $person->save();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function processXML(Request $request)
    {
        if ($request->file) {
            try {
                $lattesXML = simplexml_load_file($request->file);
                $lattes = json_decode(json_encode($lattesXML), true);
                $this->createPerson($lattes['DADOS-GERAIS'], $lattes['DADOS-COMPLEMENTARES'], $lattes['OUTRA-PRODUCAO'], $lattes['@attributes']);
                if (isset($lattes['PRODUCAO-BIBLIOGRAFICA']['ARTIGOS-PUBLICADOS'])) {
                    $this->artigos($lattes['PRODUCAO-BIBLIOGRAFICA']['ARTIGOS-PUBLICADOS'], $lattes['@attributes']);
                }
                if (isset($lattes['PRODUCAO-BIBLIOGRAFICA']['TRABALHOS-EM-EVENTOS'])) {
                    $this->trabalhosEmEventos($lattes['PRODUCAO-BIBLIOGRAFICA']['TRABALHOS-EM-EVENTOS'], $lattes['@attributes']);
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        } else {
            echo 'Não foi enviado um arquivo XML do Lattes válido.';
        }
    }
}