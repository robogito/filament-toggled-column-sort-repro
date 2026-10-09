<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SortByToggledHiddenCountColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_table_still_loads_after_the_user_hides_the_count_column_it_is_sorted_by(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(2)->for($user)->create();

        $this->actingAs($user);

        $component = Livewire::test(ListUsers::class)
            ->sortTable('posts_count')
            ->assertSuccessful();

        // Hide the column through the column manager while the table is still sorted by it,
        // the same as unticking it in the column manager dropdown.
        $columns = $component->instance()->tableColumns;

        foreach ($columns as &$column) {
            if ($column['name'] === 'posts_count') {
                $column['isToggled'] = false;
            }
        }

        $component
            ->set('tableColumns', $columns)
            ->call('applyTableColumnManager')
            ->assertSuccessful();
    }
}
