<?php

namespace App\Http\Controllers;

use App\Models\AddOn;
use App\Models\Language;
use Exception;
use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;
use Rawilk\Settings\Support\Context;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($lang = 'en',$module='general')
    {
        if($lang){
            $user = \Auth::user();
            if($user->can('language manage') )
            {
                if($module == 'general' ){
                    $dir = base_path() . '/resources/lang/' . $lang;
                }else{
                    $module = AddOn::where('name',$module)->first();
                    if($module)
                    {
                        $module= $module->module;
                        $this_module = Module::find($module);
                        $path =   $this_module->getPath();
                        $dir = $path.'/Resources/lang/' . $lang;
                    }else{
                        return redirect()->back()->with('error', __('Please active this module.'));
                    }
                }
                try{
                    if(file_exists($dir . '.json'))
                    {
                        $arrLabel = json_decode(file_get_contents($dir . '.json'));
                    }else{
                        return redirect()->back()->with('error', __('Permission denied.'));
                    }

                    $arrFiles   = array_diff(
                        scandir($dir), array(
                                        '..',
                                        '.',
                                    )
                    );
                    $arrMessage = [];
                    foreach($arrFiles as $file)
                    {
                        $fileName = basename($file, ".php");
                        $fileData = $myArray = include $dir . "/" . $file;
                        if(is_array($fileData))
                        {
                            $arrMessage[$fileName] = $fileData;
                        }
                    }
                    $langs = Language::where('code',$lang)->first();
                    $languages = Language::get()->pluck('name','code')->toArray();
                }catch(Exception $e){

                    return redirect()->back()->with('error',str_replace( array( '\'', '"', '`','{',"\n"), ' ', $e->getMessage()));
                }
                return view('lang.index', compact('user', 'lang', 'arrLabel', 'arrMessage','module','langs','languages'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function storeData(Request $request, $lang,$module ='general' )
    {
        $user = \Auth::user();
        if($user->can('language manage')){
            if($module == 'general' ){
                $dir = base_path() . '/resources/lang/';
            }else{
                $modules = AddOn::where('name',$module)->first();
                if(!empty($modules))
                {
                    $this_module = Module::find($modules->module);
                    $path =   $this_module->getPath();
                    $dir = $path.'/Resources/lang/';
                }else{
                    return redirect()->back()->with('error', __('Please active this module.'));
                }

            }
            try{

                if(!is_dir($dir))
                {
                    mkdir($dir);
                    chmod($dir, 0777);
                }
                $jsonFile = $dir . "/" . $lang . ".json";

                file_put_contents($jsonFile, json_encode($request->label));

                $langFolder = $dir . "/" . $lang;
                if(!is_dir($langFolder))
                {
                    mkdir($langFolder);
                    chmod($langFolder, 0777);
                }
                if($module == 'general' || $module == ''){
                    foreach($request->message as $fileName => $fileData)
                    {
                        $content = "<?php return [";
                        $content .= $this->buildArray($fileData);
                        $content .= "];";
                        file_put_contents($langFolder . "/" . $fileName . '.php', $content);
                    }
                }
            }catch(Exception $e){
                return redirect()->back()->with('error',$e->getMessage());
            }
            return redirect()->route('lang.index', [$lang,$module])->with('success', __('Language save successfully'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function buildArray($fileData)
    {
        $content = "";
        foreach($fileData as $lable => $data)
        {
            if(is_array($data))
            {
                $content .= "'$lable'=>[" . $this->buildArray($data) . "],";
            }
            else
            {
                $content .= "'$lable'=>'" . addslashes($data) . "',";
            }
        }

        return $content;
    }

    public function changeLang($lang)
    {
        $userContext = new Context(['user_id' => \Auth::user()->id,'workspace_id'=>getActiveWorkSpace()]);
        if($lang == "ar" || $lang == "he")
        {
            \Settings::context($userContext)->set('site_rtl', 'on');

        }
        else
        {
            \Settings::context($userContext)->set('site_rtl', 'off');
        }
        $user       = \Auth::user();
        $user->lang = $lang;
        $user->save();
        return redirect()->back()->with('success', __('Language Change Successfully!'));
    }

    public function disableLang(Request $request)
    {
        if(\Auth::user()->can('language manage'))
        {
            if($request->has('mode') && $request->lang){
                $lang = Language::where('code',$request->lang)->first();
                $lang->status = $request->mode;
                $lang->save();
            }
            if($request->mode == 0){
                $data['message'] = __('Language Disabled Successfully');
                $data['status'] = 200;
                return $data;
            }
            else
            {
                $data['message'] = __('Language Enabled Successfully');
                $data['status'] = 200;
                return $data;
            }
        }
    }

}
