<?php

namespace Tests\Feature\Office;

use App\Domains\Workspace\Models\FurnitureCategory;
use App\Domains\Workspace\Models\FurnitureItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

/**
 * Office arcade: a "Games" furniture category with one machine per game,
 * placeable from the editor; the office loads the arcade window and script.
 * (The games themselves are refereed by the realtime server, tested there.)
 */
class GameMachinesTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    public function test_the_games_category_holds_a_machine_per_game(): void
    {
        $category = FurnitureCategory::where('slug', 'games')->firstOrFail();
        $machines = FurnitureItem::where('category_id', $category->id)->orderBy('slug')->get();

        $this->assertSame(['arcade_chess', 'arcade_karts', 'arcade_pool', 'arcade_quiz', 'arcade_racing', 'arcade_run', 'arcade_snipers', 'arcade_tictactoe'], $machines->pluck('slug')->all());
        foreach ($machines as $m) {
            $this->assertSame('game', $m->interaction_type);
            $this->assertContains($m->interaction_config['game'], ['snipers', 'racing', 'karts', 'pool', 'quiz', 'run', 'chess', 'tictactoe']);
            $this->assertFileExists(public_path(ltrim($m->image_url, '/')));
        }
    }

    public function test_the_editor_offers_the_machines(): void
    {
        $this->buildProjectWorkspace();

        $this->actingAs($this->owner)->get('/editor')
            ->assertOk()
            ->assertSee('/images/furniture/games/racing.svg', false)
            ->assertSee('arcade_pool', false);
    }

    public function test_the_office_loads_the_arcade(): void
    {
        $this->buildProjectWorkspace();

        $this->actingAs($this->employee)->get('/office')
            ->assertOk()
            ->assertSee('id="game-modal"', false)
            ->assertSee('/js/office/games.js', false)
            ->assertSee('window.UlaGames.init', false);
    }
}
