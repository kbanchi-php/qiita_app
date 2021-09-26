@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                @if (!empty($errors))
                    <div class="error">
                        @foreach ($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    </div>
                @endif
                {{-- フォームの送信先はupdateアクション --}}
                <form action="{{ route('articles.update', $article->id) }}" method="post">
                    @csrf
                    @method('PATCH')
                    {{-- セレクトボックスで非公開を選択済み 開発時は間違えて変更しないようにdisabledをつける --}}
                    <select name="private" id="private">
                        <option value="true" selected>非公開</option>
                        <option value="false" disabled>公開</option>
                    </select>
                    {{-- 各要素をブロック要素にする為、divタグで囲む --}}
                    <div>
                        <input type="text" name="title" id="title" placeholder="タイトル" value="{{ $article->title }}">
                    </div>
                    <div>
                        <input type="text" name="tags" id="tags" placeholder="知識に関連するタグをスペース区切りで5つまで入力 (例: Ruby Rails)"
                            value=" {{ $article->tags }}">
                    </div>
                    <div>
                        <textarea name="body" id="body" cols="30" rows="10" +
                            placeholder="エンジニアに関わる知識をMarkdown記法で書いて共有しよう">{{ $article->body }}</textarea>
                    </div>
                    <input type="submit" value="更新する">
                </form>
                <button type="button" onclick="location.href='{{ route('articles.index') }}'">一覧へ戻る</button>
            </div>
        </div>
    </div>
@endsection
