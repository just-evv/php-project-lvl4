<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TaskTest extends TestCase
{
    private object $user;
    private object $taskStatus;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->taskStatus = TaskStatus::factory()->create();
    }

    public function testTasksIndex()
    {
        $this->get(route('tasks.index'))
            ->assertOk();
    }

    public function testSeeAsUser()
    {
        $this->actingAs($this->user)
            ->get(route('tasks.index'))
            ->assertSee('Create new task');
    }

    public function testCreateTask()
    {
        $this->get(route('tasks.create'))
            ->assertOk();
    }

    public function testStoreTask()
    {
        $assignedUser = User::factory()->create();
        $newTask = [
            'name' => 'task',
            'description' => 'description',
            'status_id' => $this->taskStatus->id,
            'assigned_to_id' => $assignedUser->id,
        ];
        $this->actingAs($this->user)
            ->post(route('tasks.store', $newTask))
            ->assertRedirect(route('tasks.index'));
        $this->get(route('tasks.index'))
            ->assertSee($newTask['name']);
        $this->assertDatabaseHas('tasks', $newTask);
    }

    public function testShowTask()
    {
        $task = Task::factory()->create();
        $this->get(route('tasks.show', $task))
            ->assertSee([$task->name, $task->description, $task->status->name]);
    }

    public function testUpdateTask()
    {
        $task = Task::factory()->create();
        $request = ['name' => 'new task',
            'description' => '',
            'status_id' => $task->status->id,
            'assigned_to_id' => $this->user->id
            ];
        $this->patch(route('tasks.update', $task), $request)
            ->assertRedirect(route('tasks.index'))
            ->assertSessionDoesntHaveErrors();
        $updatedTask = DB::table('tasks')->find($task->id);
        $this->assertEquals($request['name'], $updatedTask->name);
    }
}