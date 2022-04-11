@extends('layouts.app')

@section('content')

    <h1 class="mb-5">Create status</h1>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

{{ Form::model($taskStatus, ['route' => 'task_statuses.store']) }}
{{ Form::label('name', 'Name') }}
{{ Form::text('name') }}<br>

{{ Form::submit('Create') }}
{{ Form::close() }}

@endsection
