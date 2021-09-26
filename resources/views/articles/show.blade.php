@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <button type="button" onclick="location.href='{{ route('articles.index') }}'">一覧へ戻る</button>
                @if ($article->user->permanent_id == $user->permanent_id)
                    <button type="button"
                        onclick="location.href='{{ route('articles.edit', $article->id) }}'">編集する</button>
                    <button type="submit" form="delete-form"
                        onclick="if(!confirm('本当に削除していいですか？')){return false};">削除する</button>
                    <form action="{{ route('articles.destroy', $article->id) }}" method="post" id="delete-form">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
                <h1>{{ $article->title }}</h1>
                <div class="markdown-body">
                    {{-- {!! Str::markdown($article->body, ['html_input' => 'escape']) !!} --}}
                    {{ $article->html }}
                </div>
            </div>
        </div>
    </div>
@endsection
