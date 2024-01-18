<?php

namespace App\Http\Controllers\admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    public function index(Request $request){
        $categories = Category::latest();

        if (!empty($request->get('keyword'))){
            $categories = $categories->where('name', 'like', '%'.$request->get('keyword').'%');
        }

        $categories = $categories->paginate(10); // Corrección aquí
        return view('admin.category.list', compact('categories'));
    }

    public function create(){
        return view('admin.category.create');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories',
        ]);

        if ($validator->passes()) {

            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->save();

            //Save Image Here

            if(!empty($request->image_id)){
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.',$tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id.'.'.$ext;
                $sPath = public_path().'/temp/'.$tempImage->name;
                $dPath = public_path().'/uploads/categories/'.$tempImage->name;
                File::copy($sPath,$dPath);

                //generate image thumbail
                $dPath = public_path().'/uploads/category/thumb/'.$newImageName;
                $img = image::make($sPath);
                //$img->resize(450,600);
                $img->fit(450, 600, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($dPath);

                $category->image = $newImageName;
                $category->save();

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

    public function edit($categoryId, Request $request) {
        
        // echo $categoryId;
        $category = category::find($categoryId);
        if (empty($category)) {
            return redirect()->route('categories.index');
        }
                
        return view('admin.category.edit',compact('category'));
    }

    public function update($categoryId, Request $request)
    {
        $category = Category::find($categoryId);
    
        if (empty($category)) {
            $request->session()->flash('error', 'Categoria no encontrada');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Categoría no encontrada'
            ]);
        }
    
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $category->id . ',id',
        ]);
    
        if ($validator->passes()) {
    
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->save();
    
            $oldImage = $category->image;
    
            // delete old images here
            File::delete(public_path() . '/uploads/category/thumb/' . $oldImage);
            File::delete(public_path() . '/uploads/category/' . $oldImage);
    
            $request->session()->flash('success', 'Categoría modificada satisfactoriamente');
    
            return response()->json([
                'status' => true,
                'message' => 'Categoría modificada satisfactoriamente'
            ]);
    
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    

    public function destroy($categoryId, Request $request){
        $category = category::find($categoryId);
        if(empty($category)) {
            $request->session()->flash('error','Category not found');
            return redirect()->route('categories.index');
        }

        file::delete(public_path().'/uploads/category/thumb/'.$category->image);
        file::delete(public_path().'/uploads/category/'.$category->image);

        $category->delete();

        $request->session()->flash('success','Category deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully'
            
        ]);
    }

}