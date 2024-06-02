<?php

namespace SunrayEu\ProductDescriptionAnalyser\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SunrayEu\ProductDescriptionAnalyser\App\Jobs\AnalyzeProductDescription;
use SunrayEu\ProductDescriptionAnalyser\App\Models\Product;
use SunrayEu\ProductDescriptionAnalyser\App\Models\File;
use Illuminate\Support\Facades\DB;

class FileController extends Controller
{
    /**
     * Display the list of products for the currently selected file.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $fileHash = $this->getSelectedFileHash();
        $file = File::with('products:id,name,description,hash,score')->where('hash', $fileHash)->first();

        $products = $file ? $file->products->toArray() : [];
        return view('index', [
            'descriptions' => $products,
            'filename' => $file ? $file->name : null
        ]);
    }

    /**
     * Handle the file upload and process the contents.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $file = $request->file('file');
        $fileName = htmlspecialchars($file->getClientOriginalName());
        $fileHash = md5_file($file);

        try {
            $fileData = $this->parseFile($file);
            $fileDb = DB::transaction(function () use ($fileHash, $fileName, $fileData) {
                return $this->processFileData($fileHash, $fileName, $fileData);
            });

            $this->setSelectedFileHash($fileHash);
            $outputData = $fileDb->products->toArray();

            return view('index', [
                'descriptions' => $outputData,
                'filename' => $fileName
            ])->with('success', 'File uploaded successfully!');
        } catch (\Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage());
            return redirect()->back()->withErrors('File upload failed. Please try again.');
        }
    }

    /**
     * Re-analyze the products for the currently selected file.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reanalyse(Request $request)
    {
        $fileHash = $this->getSelectedFileHash();
        $file = File::with('products')->where('hash', $fileHash)->first();

        if ($file) {
            $file->products()->update(['score' => null]);
            foreach ($file->products as $product) {
                AnalyzeProductDescription::dispatch($product);
            }
        }

        return redirect()->route('index')->with('success', 'Reanalysis started.');
    }

    /**
     * Deselect the currently selected file.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function deselect(Request $request)
    {
        $this->removeSelectedFileHash();
        return view('index', ['descriptions' => [], 'filename' => '']);
    }

    /**
     * Parse the uploaded file and return the data.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return array
     * @throws \Exception
     */
    private function parseFile($file)
    {
        $fileHandle = fopen($file, "r");
        $data = [];
        while (($row = fgetcsv($fileHandle)) !== false) {
            $data[] = $row;
        }
        fclose($fileHandle);

        if (empty($data)) {
            throw new \Exception('The uploaded file is empty.');
        }

        return $data;
    }

    /**
     * Process the parsed file data and store it in the database.
     *
     * @param string $fileHash
     * @param string $fileName
     * @param array $fileData
     * @return File
     */
    private function processFileData($fileHash, $fileName, $fileData)
    {
        $csvHeader = array_shift($fileData);
        $file = File::firstOrCreate(['hash' => $fileHash], ['name' => $fileName]);

        foreach ($fileData as $row) {
            $productData = array_combine($csvHeader, $row);
            $this->processProductData($file, $productData);
        }

        return $file;
    }

    /**
     * Process individual product data and store it in the database.
     *
     * @param File $file
     * @param array $productData
     */
    private function processProductData(File $file, array $productData)
    {
        $productName = strip_tags($productData['name']);
        $productDescription = strip_tags($productData['description']);
        $descriptionHash = md5($productName . $productDescription);

        $product = Product::firstOrCreate(
            ['hash' => $descriptionHash],
            ['name' => $productName, 'description' => $productDescription]
        );

        $file->products()->syncWithoutDetaching([$product->id]);

        if (is_null($product->score)) {
            AnalyzeProductDescription::dispatch($product);
        }
    }

    /**
     * Get the selected file hash from the session.
     *
     * @return string|null
     */
    private function getSelectedFileHash(): ?string
    {
        return session()->get('file_hash');
    }

    /**
     * Set the selected file hash in the session.
     *
     * @param string $hash
     */
    private function setSelectedFileHash(string $hash): void
    {
        session()->put('file_hash', $hash);
    }

    /**
     * Remove the selected file hash from the session.
     */
    private function removeSelectedFileHash(): void
    {
        session()->forget('file_hash');
    }
}
