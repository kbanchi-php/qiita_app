<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use PhpParser\JsonDecoder;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 記事一覧を取得
        $method = 'GET';
        $tag_id = 'PHP';
        $per_page = 30;

        $url = config('qiita.url') . '/api/v2/tags/' . $tag_id . '/items?'
            . 'page=1&'
            . 'per_page=' . $per_page;

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $articles = json_decode($body, false);
        } catch (\Throwable $th) {
            $articles = null;
        }

        // 自分の記事一覧を取得
        $method = 'GET';
        $per_page = 30;

        $url = config('qiita.url') . '/api/v2/authenticated_user/items?'
            . 'page=1&'
            . 'per_page=' . $per_page;

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $my_articles = json_decode($body, false);
        } catch (\Throwable $th) {
            $my_articles = null;
        }

        $data = [
            'articles' => $articles,
            'my_articles' => $my_articles,
        ];

        return view('articles.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('articles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $method = 'POST';
        $url = config('qiita.url') . '/api/v2/items';

        $tag_array = explode(' ', $request->tags);
        $tags = array_map(function ($tag) {
            return ['name' => $tag];
        }, $tag_array);

        $data = [
            'title' => $request->title,
            'body' => $request->body,
            'private' => $request->private == "true" ? true : false,
            'tags' => $tags
        ];

        $options = [
            'json' => $data,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        $client = new Client();

        try {
            $client->request($method, $url, $options);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            // return back()->withErrors(['error' => $e->getResponse()->getReasonPhrase()]);
            return back()->withErrors(['error' => '記事の投稿に失敗しました。']);
        }

        return redirect()->route('articles.index')->with('flash_message', '記事の投稿に成功しました。');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $method = 'GET';
        $url = config('qiita.url') . '/api/v2/items/' . $id;

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $article = json_decode($body, false);

            // 変換するクラスをインスタンス化して設定を追加
            $parser = new \cebe\markdown\GithubMarkdown();
            $parser->keepListStartNumber = true;  // olタグの番号の初期化を有効にする
            $parser->enableNewlines = true;  // 改行を有効にする
            // MarkdownをHTML文字列に変換し、HTMLに変換(エスケープする)
            $html_string = $parser->parse($article->body);
            $article->html = new \Illuminate\Support\HtmlString($html_string);
        } catch (\Throwable $th) {
            return back();
        }

        $method = 'GET';

        // QIITA_URLの値を取得してURLを定義
        $url = config('qiita.url') . '/api/v2/authenticated_user/';

        // $optionsにトークンを指定
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        // Client(接続する為のクラス)を生成
        $client = new Client();

        try {
            // データを取得し、JSON形式からPHPの変数に変換
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $user = json_decode($body, false);
        } catch (\Throwable $th) {
            return back();
        }

        $data = [
            'article' => $article,
            'user' => $user,
        ];

        return view('articles.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $method = 'GET';

        // QIITA_URLの値を取得してURLを定義
        $url = config('qiita.url') . '/api/v2/items/' . $id;

        // $optionsにトークンを指定
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        // Client(接続する為のクラス)を生成
        $client = new Client();

        try {
            // データを取得し、JSON形式からPHPの変数に変換
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $article = json_decode($body, false);

            // tagsを配列からスペース区切りに変換
            $tag_array = array_map(function ($tag) {
                return $tag->name;
            }, $article->tags);
            $article->tags = implode(' ', $tag_array);
        } catch (\Throwable $th) {
            return back();
        }

        return view('articles.edit')->with(compact('article'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $method = 'PATCH';

        // QIITA_URLの値を取得してURLを定義
        $url = config('qiita.url') . '/api/v2/items/' . $id;

        // スペース区切りの文字列を配列に変換し、JSON形式に変換
        $tag_array = explode(' ', $request->tags);
        $tags = array_map(function ($tag) {
            return ['name' => $tag];
        }, $tag_array);

        // 送信するデータを整形
        $data = [
            'title' => $request->title,
            'body' => $request->body,
            'private' => $request->private == "true" ? true : false,
            'tags' => $tags
        ];

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $data,
        ];

        // Client(接続する為のクラス)を生成
        $client = new Client();

        try {
            // データを取得し、JSON形式からPHPの変数に変換
            $response = $client->request($method, $url, $options);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return back()->withErrors(['error' => $e->getResponse()->getReasonPhrase()]);
        }
        return redirect()->route('articles.index')->with('flash_message', '記事の更新に成功しました。');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $method = 'DELETE';

        // QIITA_URLの値を取得してURLを定義
        $url = config('qiita.url') . '/api/v2/items/' . $id;

        // $optionsにトークンを指定
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        // Client(接続する為のクラス)を生成
        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return back()->withErrors(['error' => $e->getResponse()->getReasonPhrase()]);
        }

        return redirect()->route('articles.index')->with('flash_message', '記事を削除しました');
    }
}
