<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Projects\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_create_project_with_auto_slug(): void
    {
        Livewire::test(CreateProject::class)
            ->fillForm([
                'title' => 'Amenajare parc central Slatina',
                'location' => 'Primăria Slatina, Olt',
                'client_type' => 'primarie',
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $project = Project::where('title', 'Amenajare parc central Slatina')->sole();
        $this->assertSame('amenajare-parc-central-slatina', $project->slug);
        $this->assertTrue($project->is_published);
    }

    public function test_list_shows_projects_and_filters(): void
    {
        $pub = Project::create(['title' => 'Publicat', 'slug' => 'pub', 'is_published' => true, 'sort_order' => 1]);
        $draft = Project::create(['title' => 'Nepublicat', 'slug' => 'draft', 'is_published' => false, 'sort_order' => 2]);

        Livewire::test(ListProjects::class)
            ->assertCountTableRecords(2)
            ->assertCanRenderTableColumn('title')
            ->assertCanRenderTableColumn('primary_image_path')
            ->filterTable('is_published', true)
            ->assertCanSeeTableRecords([$pub])
            ->assertCanNotSeeTableRecords([$draft]);
    }

    public function test_images_relation_manager_lists(): void
    {
        $project = Project::create(['title' => 'Cu poze', 'slug' => 'cu-poze', 'is_published' => true, 'sort_order' => 1]);
        $project->images()->create(['path' => 'projects/cu-poze/1.jpg', 'is_primary' => true, 'sort_order' => 1]);

        Livewire::test(ImagesRelationManager::class, [
            'ownerRecord' => $project,
            'pageClass' => EditProject::class,
        ])
            ->assertCountTableRecords(1)
            ->assertCanRenderTableColumn('path')
            ->assertCanRenderTableColumn('is_primary');
    }
}
