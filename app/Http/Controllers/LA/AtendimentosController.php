<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;
use Validator;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;

use App\Models\Atendimento;

class AtendimentosController extends Controller
{
	public $show_action = true;
	public $view_col = 'aplicador';
	public $listing_cols = ['id', 'aplicador', 'paciente', 'data', 'observacoes'];
	
	public function __construct() {
		// Field Access of Listing Columns
		if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('Atendimentos', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('Atendimentos', $this->listing_cols);
		}
	}
	
	/**
	 * Display a listing of the Atendimentos.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Atendimentos');
		
		if(Module::hasAccess($module->id)) {
			return View('la.atendimentos.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Show the form for creating a new atendimento.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created atendimento in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if(Module::hasAccess("Atendimentos", "create")) {
		
			$rules = Module::validateRules("Atendimentos", $request);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}
			
			$insert_id = Module::insert("Atendimentos", $request);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.atendimentos.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Display the specified atendimento.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if(Module::hasAccess("Atendimentos", "view")) {
			
			$atendimento = Atendimento::find($id);
			if(isset($atendimento->id)) {
				$module = Module::get('Atendimentos');
				$module->row = $atendimento;
				
				return view('la.atendimentos.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('atendimento', $atendimento);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("atendimento"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Show the form for editing the specified atendimento.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if(Module::hasAccess("Atendimentos", "edit")) {			
			$atendimento = Atendimento::find($id);
			if(isset($atendimento->id)) {	
				$module = Module::get('Atendimentos');
				
				$module->row = $atendimento;
				
				return view('la.atendimentos.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
				])->with('atendimento', $atendimento);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("atendimento"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Update the specified atendimento in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if(Module::hasAccess("Atendimentos", "edit")) {
			
			$rules = Module::validateRules("Atendimentos", $request, true);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}
			
			$insert_id = Module::updateRow("Atendimentos", $request, $id);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.atendimentos.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Remove the specified atendimento from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if(Module::hasAccess("Atendimentos", "delete")) {
			Atendimento::find($id)->delete();
			
			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.atendimentos.index');
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}
	
	/**
	 * Datatable Ajax fetch
	 *
	 * @return
	 */
	public function dtajax()
	{
		$values = DB::table('atendimentos')->select($this->listing_cols)->whereNull('deleted_at');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('Atendimentos');
		
		for($i=0; $i < count($data->data); $i++) {
			for ($j=0; $j < count($this->listing_cols); $j++) { 
				$col = $this->listing_cols[$j];
				if($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="'.url(config('laraadmin.adminRoute') . '/atendimentos/'.$data->data[$i][0]).'">'.$data->data[$i][$j].'</a>';
				}
				// else if($col == "author") {
				//    $data->data[$i][$j];
				// }
			}
			
			if($this->show_action) {
				$output = '';
				if(Module::hasAccess("Atendimentos", "edit")) {
					$output .= '<a href="'.url(config('laraadmin.adminRoute') . '/atendimentos/'.$data->data[$i][0].'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}
				
				if(Module::hasAccess("Atendimentos", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.atendimentos.destroy', $data->data[$i][0]], 'method' => 'delete', 'id' => 'target'.$data->data[$i][0], 'style'=>'display:inline']);
					$output .= ' <button class="btn btn-danger btn-xs" type="button" onclick="confirmacao('.$data->data[$i][0].');"><i class="fa fa-times"></i></button>';
					$output .= Form::close();
				}
				$data->data[$i][] = (string)$output;
			}
		}
		$out->setData($data);
		return $out;
	}
}
