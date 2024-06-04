<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use SunrayEu\ProductDescriptionAnalyser\App\Models\File;
use SunrayEu\ProductDescriptionAnalyser\App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_the_list_of_products_for_the_selected_file()
    {
        $file = File::factory()->hasProducts(3)->create();
        session(['file_hash' => $file->hash]);

        $response = $this->get('/');

        $file->products->each(function ($item) {
            $item->makeHidden('created_at');
            $item->makeHidden('updated_at');
        });

        $response->assertStatus(200);
        $response->assertViewHas('descriptions', $file->products->toArray());
        $response->assertViewHas('filename', $file->name);
    }

    public function test_it_uploads_a_file_and_processes_its_contents()
    {
        Storage::fake('local');

        $fileContent = "name,description\nProduct1,Description1\nProduct2,Description2";
        $uploadedFile = UploadedFile::fake()->createWithContent('test.csv', $fileContent);

        $response = $this->post('/upload', ['file' => $uploadedFile]);

        $response->assertStatus(200);
        $response->assertViewHas('descriptions');
        $response->assertViewHas('filename', 'test.csv');

        $this->assertDatabaseHas('files', ['name' => 'test.csv']);
        $this->assertDatabaseHas('products', ['name' => 'Product1', 'description' => 'Description1']);
        $this->assertDatabaseHas('products', ['name' => 'Product2', 'description' => 'Description2']);
    }

    public function test_it_validates_the_uploaded_file()
    {
        $response = $this->post('/upload', ['file' => 'not_a_file']);

        $response->assertSessionHasErrors(['file']);
    }
}
