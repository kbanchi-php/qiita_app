@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                @if (session('flash_message'))
                    <div class="flash_message">
                        {{ session('flash_message') }}
                    </div>
                @endif
                <h1>記事一覧</h1>
                @if (!empty($articles))
                    <ul>
                        @foreach ($articles as $article)
                            <li>
                                <a href="{{ route('articles.show', $article->id) }}">
                                    {{ $article->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <hr>
                <h1>自分の記事一覧</h1>
                @if (!empty($my_articles))
                    <ul>
                        @foreach ($my_articles as $my_article)
                            <li>
                                <a href="{{ route('articles.show', $my_article->id) }}">
                                    {{ $my_article->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <button type="button" onclick="location.href='{{ route('articles.create') }}'">記事投稿</button>
            </div>
        </div>
    </div>
@endsection
