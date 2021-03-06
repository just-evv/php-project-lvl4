@extends('layouts.app')

@section('content')

    @include('flash::message')
    <h1 class="mb-5">{{__('Tasks')}}</h1>

    <div class="d-flex mb-3">
            <!-- Form -->
        {{ Form::open(['route' => 'tasks.index', 'method' => 'GET']) }}

        <div class="row g-1">

            <div class="col">
                {{ Form::select('filter[status_id]', $statuses , null, ['placeholder' => __('content.item.status'), 'class' => "form-select me-2"]) }}
            </div>
            <div class="col">
                {{ Form::select('filter[created_by_id]', $users , null, ['placeholder' => __('content.item.created_by'), 'class' => "form-select me-2"]) }}
            </div>
            <div class="col">
                {{ Form::select('filter[assigned_to_id]', $users , null, ['placeholder' => __('content.item.assigned_to'), 'class' => "form-select me-2"]) }}
            </div>
            <div class="col">
                {{ Form::submit(__('Apply'), ['class' => 'btn btn-outline-primary me-2']) }}
            </div>
        {{ Form::close() }}
        </div>

        @can('create', App\Models\Task::class)
            <div class="ms-auto">
                <a href="{{ route('tasks.create') }}" class="btn btn-primary ml-auto">{{__('content.task.create')}}</a>
            </div>
        @endcan
    </div>

    <table class="table me-2">
        <thead>
            <tr>
            <th>{{__('ID')}}</th>
            <th>{{ __('content.item.status') }}</th>
            <th>{{ __('content.item.name') }}</th>
            <th>{{ __('content.item.created_by') }}</th>
            <th>{{ __('content.item.assigned_to') }}</th>
            <th>{{ __('content.item.created_at') }}</th>
            @canany(['update', 'delete'], App\Models\Task::class)
                <th>{{__('Action')}}</th>
            @endcanany
        </tr>
        </thead>

        <tbody>
            @foreach ($filter as $task)
            <tr>
                <td>{{ $task->id }}</td>
                <td>{{ $task->status->name }}</td>
                <td>
                    <a class="text-decoration-none" href="{{ route('tasks.show', $task) }}">
                        {{ $task->name }}
                    </a>
                </td>
                <td>{{ $task->creator->name }}</td>
                <td>{{ $task->assignedUser->name ?? ''}}</td>
                <td>{{ $task->created_at->format('d.m.Y') }}</td>
                @can('update', $task)
                    <td>
                        <a class="text-decoration-none" href="{{ route('tasks.edit', $task) }}">{{ __('Edit') }}</a>
                        @can('delete', $task)
                            <a class="text-danger text-decoration-none"
                               href="{{ route('tasks.destroy', $task) }}"
                               data-confirm="{{ __("Are you sure?") }}"
                               data-method="delete"
                               rel="nofollow">
                                {{__('Delete')}}
                            </a>
                        @endcan
                    </td>
                @endcan
            </tr>
        @endforeach
        </tbody>

    </table>

    {{ $filter->links('pagination::bootstrap-4') }}

@endsection
