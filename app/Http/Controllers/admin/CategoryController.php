<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Categoria;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    public function index(Request $request){
        $categorias = Categoria::latest();

        if (!empty($request->get('keyword'))){
            $categorias = $categorias->where('name', 'like', '%'.$request->get('keyword').'%');
        }

        $categorias = $categorias->paginate(10); // Corrección aquí
        return view('admin.categorias.list', compact('categorias'));
    }

    public function create(){
        return view('admin.categorias.create');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categorias',
        ]);

        if ($validator->passes()) {

            $categoria = new Categoria();
            $categoria->name = $request->name;
            $categoria->slug = $request->slug;
            $categoria->status = $request->status;
            $categoria->save();

            //Save Image Here

            if(!empty($request->image_id)){
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.',$tempImage->name);
                $ext = last($extArray);

                $newImageName = $categoria->id.'.'.$ext;
                $sPath = public_path().'/temp/'.$tempImage->name;
                $dPath = public_path().'/uploads/categorias/'.$tempImage->name;
                File::copy($sPath,$dPath);

            }

            $request->session()->flash('success', 'Categoria agregada satisfactoriamente');

            return response()->json([
                'status' => true,
                'message' => 'Categoria agregada satisfactoriamente'
                
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
                
            ]);
        }
    }

    public function edit(){
        // Implementa la lógica de edición
    }

    public function update(Request $request, $id){
        // Implementa la lógica para actualizar la categoría con el ID proporcionado
    }

    public function destroy(Request $request, $id){
        // Implementa la lógica para eliminar la categoría con el ID proporcionado
    }
}
