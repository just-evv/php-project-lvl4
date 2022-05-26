<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(): View|Factory|Application
    {
        $statuses = TaskStatus::pluck('name', 'id');
        $users = User::pluck('name', 'id');

        $filter = QueryBuilder::for(Task::class)
            ->allowedFilters([
                AllowedFilter::exact('status_id'),
                AllowedFilter::exact('created_by_id'),
                AllowedFilter::exact('assigned_to_id')])
            ->paginate();

        return view('tasks.index', compact('filter', 'statuses', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(): View|Factory|Application
    {
        $task = new Task();
        $statuses = TaskStatus::pluck('name', 'id');
        $users = User::pluck('name', 'id');
        $labels = Label::pluck('name', 'id');

        return view('tasks.create', compact(['task', 'statuses', 'users', 'labels']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->cannot('create', Task::class)) {
            abort(403);
        }
        $data = $this->validate($request, [
            'name' => 'required|unique:tasks',
            'description' => 'nullable|max:255',
            'status_id' => 'required',
            'assigned_to_id' => 'nullable',
        ]);

        $userId = Auth::id();
        $user = User::find($userId);
        $status = TaskStatus::find($data['status_id']);
        $assignedUser = User::find($data['assigned_to_id']);

        $newTask = new Task($data);

        $newTask->creator()->associate($user);
        $newTask->status()->associate($status);
        $newTask->assignedUser()->associate($assignedUser);
        $newTask->save();

        $newTask->labels()->attach($request->labels);
        $newTask->save();

        flash(__('messages.created', ['name' => 'task']));

        return redirect()->route('tasks.index');
    }

    /**
     * Display the specified resource.
     *
     * @param Task $task
     * @return Application|Factory|View
     */
    public function show(Task $task): View|Factory|Application
    {
        $task = Task::findOrFail($task->id);
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Task $task
     * @return Application|Factory|View
     */
    public function edit(Task $task): View|Factory|Application
    {
        $task = Task::findOrFail($task->id);
        $statuses = TaskStatus::pluck('name', 'id');
        $allUsers = User::pluck('name', 'id');
        return view('tasks.edit', compact(['task', 'statuses', 'allUsers']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Task $task
     * @return RedirectResponse
     */
    public function update(Request $request, Task $task): RedirectResponse
    {

        $task = Task::findOrFail($task->id);
        $data = $this->validate($request, [
            'name' => 'required|unique:tasks,name,' . $task->id,
            'description' => 'nullable|max:255',
            'status_id' => 'required',
            'assigned_to_id' => 'nullable'
        ]);

        $task->fill($data);
        $status = TaskStatus::find($data['status_id']);
        $assignedUser = User::find($data['assigned_to_id']);
        $task->status()->associate($status);
        $task->assignedUser()->associate($assignedUser);

        $task->save();

        flash(__('messages.updated', ['name' => 'task']));

        return redirect()->route('tasks.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Task $task
     * @return RedirectResponse
     */
    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $task = Task::findOrFail($task->id);
        $task->delete();
        flash(__('messages.deleted', ['name' => 'task']));
        return redirect()->route('tasks.index');
    }
}
