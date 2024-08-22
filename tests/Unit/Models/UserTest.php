<?php

namespace Tests\Unit\Models;

use App\Models\Terminal;
use App\Models\Trace;
use App\Models\User;
use App\TraceLinkType;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_it_returns_terminals()
    {
        // Arrange
        $user = User::withoutEvents(function () {
            return User::factory()->create();
        });

        $terminal = Terminal::factory()->for($user)->create();

        // Act
        $result = $user->terminals;

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals($terminal->id, $result->first()->id);
    }

    public function test_it_returns_traces(): void
    {
        // Arrange
        $user = User::factory()->create();
        $trace = Trace::factory()->for($user->terminals->first())->create([
            'traceable_id' => $user->id,
            'traceable_type' => User::class,
        ]);

        // Act
        $traces = $user->traces;

        // Assert
        $this->assertCount(1, $traces);
        $this->assertEquals($trace->id, $traces->first()->id);
        $this->assertEquals($user->id, $traces->first()->traceable_id);
    }

    public function test_it_returns_linkable_traces(): void
    {
        // Arrange
        $user = User::factory()->create();
        $trace = Trace::factory()->for($user->terminals->first())->create([
            'traceable_id' => $user->id,
            'traceable_type' => User::class,
        ]);

        $user->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $user->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals($trace->id, $linkableTraces->first()->id);
    }

    public function test_it_returns_linkable_traces_with_link_type(): void
    {
        // Arrange
        $user = User::factory()->create();
        $trace = Trace::factory()->for($user->terminals->first())->create([
            'traceable_id' => $user->id,
            'traceable_type' => User::class,
        ]);

        $user->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $user->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals(TraceLinkType::Related->value, $linkableTraces->first()->pivot->trace_link_type);
        $this->assertTrue($linkableTraces->contains($trace));
    }
}
