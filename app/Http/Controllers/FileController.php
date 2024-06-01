<?php

namespace SunrayEu\ProductDescriptionAnalyser\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SunrayEu\ProductDescriptionAnalyser\App\Jobs\AnalyzeProductDescription;
use SunrayEu\ProductDescriptionAnalyser\App\Models\Product;
use SunrayEu\ProductDescriptionAnalyser\App\Models\File;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        // Validation
        $request->validate(['file' => 'required|file|mimes:csv,txt']);

        $file = $request->file('file');
        $file_md5 = md5_file($file);
        $file_data = [];
        $file_handle = fopen($file, "r");
        $file_name = htmlspecialchars($file->getClientOriginalName());

        // Loop through each line in the file
        while (($row = fgetcsv($file_handle)) !== FALSE) {
            $file_data[] = $row;
        }
        $csv_header = array_shift($file_data);

        // get file or create in db based on md5 hash
        $file_db = File::firstOrCreate(
            ['hash' => $file_md5],
            ['name' => $file_name]
        );
        $file_hash_products = null;

        if (!$file_db->wasRecentlyCreated) {
            //? File is new, so for sure we do not have connections
            $file_hash_products = $file_db->products()->get(['hash', 'score']);
        }

        $output_data = [];

        foreach ($file_data as $row) {
            $csv_product_data = array_combine($csv_header, $row);

            // TODO: create table with Products and add foreign key for descriptions as there can be duplicates
            // TODO: For now, used md5 of name + description
            $product_name = strip_tags($csv_product_data['name']);
            $product_description = strip_tags($csv_product_data['description']);
            $description_hash = md5($product_name . $product_description);

            $file_data_obj = [
                'name' => $product_name,
                'hash' => $description_hash,
                'description' => $product_description,
                'score' => null
            ];

            $product_from_db = null;

            if ($file_hash_products)
                $product_from_db = $file_hash_products->firstWhere('hash', '=', $description_hash);

            // If the product does not exist from 'product belongsTo file' list, then try to get it by hash
            if (!$product_from_db) {
                $product_from_db = Product::firstWhere('hash', '=', $description_hash);

                // TODO: there can be duplicates in name + desc md5, find solution
                // If it exist by hash, attach to file
                if ($product_from_db) {
                    $file_db->products()->attach($product_from_db->id);
                }
            }

            if ($product_from_db) {
                $file_data_obj['score'] = $product_from_db['score'];
            } else {
                $product_from_db = $file_db->products()->create([
                    'name' => $product_name,
                    'description' => $product_description,
                    'hash' => $description_hash
                ]);

                AnalyzeProductDescription::dispatch($product_from_db);
            }

            $output_data[] = $file_data_obj;
        }


        // here set file to session
        $request->session()->put('file_hash', $file_md5);

        return redirect('/')->with('success', 'File uploaded successfully!')
            ->with('descriptions', $output_data)
            ->with('filename', $file_name);
    }

    public function index(Request $request)
    {
        $products_data = [];

        // Get the file hash
        $fileHash = $request->session()->get('file_hash');

        $file_db = File::firstWhere('hash', '=', $fileHash);

        if ($file_db) {
            $products_data = $file_db->products();
            $products_data = $products_data->get(['name', 'description', 'hash', 'score'])->toArray();
        }
        // TODO: add here analyser run for not analysed descriptions

        return view('upload', [
            'descriptions' => $products_data,
            'filename' => $file_db ? $file_db->name : null
        ]);
    }
}
