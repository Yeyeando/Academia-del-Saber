<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Curso;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CursoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_ver_el_listado_de_cursos()
    {
        Curso::factory()->count(5)->create();

        $response = $this->get(route('cursos.index'));

        $response->assertStatus(200);
        $response->assertViewIs('cursos.index');
    }

    /** @test */
    public function puede_ver_un_curso_individual()
    {
        $curso = Curso::factory()->create(['nombre' => 'Curso Test']);

        $response = $this->get(route('cursos.show', $curso));

        $response->assertStatus(200);
        $response->assertSee('Curso Test');
    }

    /** @test */
    public function admin_puede_crear_curso()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $categoria = Categoria::factory()->create();

        // Simular la carga de una foto
        Storage::fake('public');
        $foto = UploadedFile::fake()->image('curso.jpg');

        $response = $this->actingAs($admin)->post(route('cursos.store'), [
            'nombre' => 'Curso Nuevo',
            'precio' => 99.99,
            'vacantes' => 50,
            'categoria_id' => $categoria->id,
            'foto' => $foto,
            'fecha_inicio' => '2025-12-01',
            'fecha_fin' => '2025-12-31',
        ]);

        $response->assertRedirect(route('cursos.index'));
        $this->assertDatabaseHas('cursos', ['nombre' => 'Curso Nuevo']);
        Storage::disk('public')->assertExists('cursos/' . $foto->hashName());

    }

    /** @test */
    public function usuario_normal_no_puede_crear_curso()
    {
        $user = User::factory()->create(['role' => 'user']);
        $categoria = Categoria::factory()->create();

        // Simular la carga de una foto
        Storage::fake('public');
        $foto = UploadedFile::fake()->image('curso.jpg');

        $response = $this->actingAs($user)->post(route('cursos.store'), [
            'nombre' => 'Curso Nuevo',
            'precio' => 99.99,
            'vacantes' => 50,
            'categoria_id' => $categoria->id,
            'foto' => $foto,
            'fecha_inicio' => '2025-12-01',
            'fecha_fin' => '2025-12-31',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function visitante_no_puede_crear_curso()
    {
        $categoria = Categoria::factory()->create();

        // Simular la carga de una foto
        Storage::fake('public');
        $foto = UploadedFile::fake()->image('curso.jpg');

        $response = $this->post(route('cursos.store'), [
            'nombre' => 'Curso Nuevo',
            'precio' => 99.99,
            'vacantes' => 50,
            'categoria_id' => $categoria->id,
            'foto' => $foto,
            'fecha_inicio' => '2025-12-01',
            'fecha_fin' => '2025-12-31',
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function la_paginacion_funciona()
    {
        Curso::factory()->count(20)->create();

        $response = $this->get(route('cursos.index'));

        $response->assertStatus(200);
        $cursos = $response->viewData('cursos');
        $this->assertCount(10, $cursos);
    }

    /** @test */
    public function la_busqueda_funciona()
    {
        Curso::factory()->create(['nombre' => 'Laptop Dell']);
        Curso::factory()->create(['nombre' => 'Mouse Logitech']);

        $response = $this->get(route('cursos.index', ['busqueda' => 'laptop']));

        $response->assertStatus(200);
        $response->assertSee('Laptop Dell');
        $response->assertDontSee('Mouse Logitech');
    }

    /** @test */
    public function el_filtro_por_categoria_funciona()
    {
        $electronica = Categoria::factory()->create(['nombre' => 'Electrónica']);
        $ropa = Categoria::factory()->create(['nombre' => 'Ropa']);

        Curso::factory()->create(['nombre' => 'Laptop', 'categoria_id' => $electronica->id]);
        Curso::factory()->create(['nombre' => 'Camisa', 'categoria_id' => $ropa->id]);

        $response = $this->get(route('cursos.index', ['categoria_id' => $electronica->id]));

        $response->assertStatus(200);
        $response->assertSee('Laptop');
        $response->assertDontSee('Camisa');
    }

    /** @test */
    public function admin_puede_eliminar_curso()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $curso = Curso::factory()->create();

        $response = $this->actingAs($admin)->delete(route('cursos.destroy', $curso));

        $response->assertRedirect(route('cursos.index'));
        $this->assertDatabaseMissing('cursos', ['id' => $curso->id]);
    }

    /** @test */
    public function usuario_normal_no_puede_eliminar_curso()
    {
        $user = User::factory()->create(['role' => 'user']);
        $curso = Curso::factory()->create();

        $response = $this->actingAs($user)->delete(route('cursos.destroy', $curso));

        $response->assertStatus(403);
        $this->assertDatabaseHas('cursos', ['id' => $curso->id]);
    }
}
