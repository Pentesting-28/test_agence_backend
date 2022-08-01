<?php

namespace App\Http\Controllers\Api\V1\Consultant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/* IMPORTACION DE MODELOS*/

/* OTRAS IMPORTACIONES */
use Illuminate\Support\Facades\DB;
use Exception;

class ConsultantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $consultant = DB::table('cao_usuario')
                ->join('permissao_sistema', function ($join) {
                    $join->on('cao_usuario.co_usuario', '=', 'permissao_sistema.co_usuario')
                            ->where('permissao_sistema.co_sistema', '=', 1)
                            ->where('permissao_sistema.in_ativo', '=', 'S');
                })
                ->where('permissao_sistema.co_tipo_usuario', '=', 0)
                ->OrWhere('permissao_sistema.co_tipo_usuario', '=', 1)
                ->OrWhere('permissao_sistema.co_tipo_usuario', '=', 2)
                ->select(
                    'cao_usuario.co_usuario',
                    'cao_usuario.no_usuario',
                    'permissao_sistema.co_usuario',
                    'permissao_sistema.co_sistema',
                    'permissao_sistema.in_ativo',
                    'permissao_sistema.co_tipo_usuario'
                )
                ->orderBy('no_usuario','asc')
                ->get();
            return response()->json([
                'message' => 'Listado de de consultores',
                'data' => $consultant,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'consultant.index.failed',
                'message' => $e->getMessage(),
            ], 505);
        }
    }

    /**
     * reportConsultant data query per month.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response //GROUP_CONCAT()
     */
    public function reportConsultant(Request $request)
    {
        try {
            $report_consultant = DB::table('cao_os')
                ->whereIn('cao_os.co_usuario', $request->item_consultants)
                ->join('cao_fatura', 'cao_os.co_os', '=', 'cao_fatura.co_os')
                ->join('cao_salario', 'cao_os.co_usuario', '=', 'cao_salario.co_usuario')
                ->select(
                    DB::raw('any_value(cao_salario.co_usuario) as co_usuario'),
                    DB::raw("DATE_FORMAT(cao_fatura.data_emissao,'%Y-%m') as month"),
                    DB::raw('sum(cao_fatura.valor) as total_valor'),//total valor
                    DB::raw('sum(cao_fatura.total_imp_inc) as total_imp'),//total impuesto
                    DB::raw('(sum(cao_fatura.valor) - sum(cao_fatura.total_imp_inc)) as total_net_earnings'),//total ganancias netas
                    DB::raw('any_value(cao_salario.brut_salario) as fixed_cost'),//Costo Fijo salrio
                    DB::raw('sum(cao_fatura.comissao_cn) as total_comissao_cn'), //total impuestos
                    DB::raw('( (sum(cao_fatura.valor) - ( sum(cao_fatura.valor) * sum(cao_fatura.total_imp_inc) ) ) * sum(cao_fatura.comissao_cn) ) as total_commission'),//total comisión
                    DB::raw('( ( sum(cao_fatura.valor) - sum(cao_fatura.total_imp_inc) ) - ( any_value(cao_salario.brut_salario) + ( ( sum(cao_fatura.valor) - ( sum(cao_fatura.valor) * sum(cao_fatura.total_imp_inc) ) ) * sum(cao_fatura.comissao_cn) ) ) ) as total_lucro ')////total Lucro
                )
                ->groupByRaw('co_usuario, month')
                ->orderBy('co_usuario', 'asc')->get();
            return response()->json([
                'message' => 'Listado de performance comercial por consultores',
                'data' => $report_consultant,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'consultant.reportConsultant.failed',
                'message' => $e->getMessage(),
            ], 505);
        }
    }

    /**
     * netEarnings data query per month.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function totalNetEarningsFixedCost(Request $request)
    {
        try {
            $consultant = DB::table('cao_os')
                ->whereIn('cao_os.co_usuario', $request->item_consultants)
                ->join('cao_fatura', 'cao_os.co_os', '=', 'cao_fatura.co_os')
                ->join('cao_salario', 'cao_os.co_usuario', '=', 'cao_salario.co_usuario')
                ->select(
                    DB::raw('any_value(cao_salario.co_usuario) as co_usuario'),
                    DB::raw("DATE_FORMAT(cao_fatura.data_emissao,'%Y-%m') as month"),
                    DB::raw('any_value(cao_salario.brut_salario) as fixed_cost'),//Costo Fijo salrio
                    DB::raw('(sum(cao_fatura.valor) - sum(cao_fatura.total_imp_inc)) as total_net_earnings')//total ganancias netas
                )
                ->groupByRaw('co_usuario, month')
                ->orderBy('co_usuario', 'asc')->get();
            return response()->json([
                'message' => 'Total de ganancias netas y costo fijo por consultores',
                'data' => $consultant,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'consultant.netEarnings.failed',
                'message' => $e->getMessage(),
            ], 505);
        }
    }

    /**
     * fixedCost data query per month.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function totalNetEarnings(Request $request)
    {
        try {
            $consultant = DB::table('cao_os')
                ->whereIn('cao_os.co_usuario', $request->item_consultants)
                ->join('cao_fatura', 'cao_os.co_os', '=', 'cao_fatura.co_os')
                ->join('cao_salario', 'cao_os.co_usuario', '=', 'cao_salario.co_usuario')
                ->select(
                    DB::raw('any_value(cao_salario.co_usuario) as co_usuario'),
                    DB::raw("DATE_FORMAT(cao_fatura.data_emissao,'%Y-%m') as month"),
                    DB::raw('(sum(cao_fatura.valor) - sum(cao_fatura.total_imp_inc)) as total_net_earnings')//total ganancias netas
                )
                ->groupByRaw('co_usuario, month')
                ->orderBy('co_usuario', 'asc')->get();
            return response()->json([
                'message' => 'Total de ganancias netas por consultores',
                'data' => $consultant,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'consultant.netEarnings.failed',
                'message' => $e->getMessage(),
            ], 505);
        }
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
}
