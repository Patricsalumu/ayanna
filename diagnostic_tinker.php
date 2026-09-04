<?php
use Illuminate\Support\Facades\DB;
$cols=function($t){return DB::getSchemaBuilder()->getColumnListing($t);};
$pick=function($a,$c,$d=null){foreach($a as $x){if(in_array($x,$c,true))return $x;}return $d;};
$sc=$cols('stock_journalier');$pc=$cols('paniers');$cc=$cols('commandes');
$pos=DB::table('stock_journalier')->select('point_de_vente_id',DB::raw('COUNT(*) as n'))->groupBy('point_de_vente_id')->orderByDesc('n')->first();
if(!$pos){echo "Aucune ligne stock_journalier\n";}else{
 $ord=in_array('created_at',$sc,true)?'created_at':'id';
 $rows=DB::table('stock_journalier')->where('point_de_vente_id',$pos->point_de_vente_id)->orderByDesc($ord)->get();$r=$rows->first();
 $s=$pick(['session','session_id','date_session','jour','date'],$sc);$session=$s?$r->{$s}:null;
 $o=$pick(['heure_ouverture','opened_at','date_ouverture','ouverture'],$sc);$f=$pick(['heure_fermeture','closed_at','date_fermeture','fermeture'],$sc);
 $open=$o?$r->{$o}:($rows->last()->created_at??null);$close=$f?$r->{$f}:($r->created_at??null);
 $open=date('Y-m-d H:i:s',strtotime($open));$close=date('Y-m-d H:i:s',strtotime($close));if(strtotime($close)<strtotime($open))$close=date('Y-m-d H:i:s');
 $j=in_array('commande_id',$pc,true)?'commande_id':(in_array('command_id',$pc,true)?'command_id':null);$st=$pick(['status','statut'],$pc,'status');$pm=$pick(['mode_paiement','mode_payment','payment_mode'],$pc);$cm=$pick(['mode_paiement','mode_payment','payment_mode'],$cc);
 $ds=[];foreach(['created_at','updated_at'] as $x)if(in_array($x,$pc,true))$ds[]='p.'.$x;if($j&&in_array('created_at',$cc,true))$ds[]='c.created_at';
 $win=function($q)use($ds,$open,$close){if($ds)$q->where(function($w)use($ds,$open,$close){foreach($ds as $i=>$x){if($i===0)$w->whereBetween($x,[$open,$close]);else $w->orWhereBetween($x,[$open,$close]);}});return $q;};
 $q=DB::table('paniers as p');if($j)$q->leftJoin('commandes as c','c.id','=','p.'.$j);$q->whereIn('p.'.$st,['valide','validé']);$win($q);
 $sel=['p.id as panier_id','p.'.$st.' as status'];if($pm)$sel[]='p.'.$pm.' as panier_mode';if($j&&$cm)$sel[]='c.'.$cm.' as commande_mode';foreach(['total_remise','total_ttc'] as $x)if(in_array($x,$pc,true))$sel[]='p.'.$x;if(in_array('created_at',$pc,true))$sel[]='p.created_at as panier_created_at';if($j&&in_array('created_at',$cc,true))$sel[]='c.created_at as commande_created_at';$items=$q->get($sel);
 $a=DB::table('paniers as p');if($j)$a->leftJoin('commandes as c','c.id','=','p.'.$j);$win($a);$all=$a->get(['p.*']);$credit=0;foreach($items as $x){$m=strtolower(($x->panier_mode??'').' '.($x->commande_mode??''));if(str_contains($m,'compte_client')||str_contains($m,'credit')||str_contains($m,'crédit'))$credit+=(float)($x->total_ttc??0);}
 echo 'point_de_vente_id='.$pos->point_de_vente_id.' session='.json_encode($session,JSON_UNESCAPED_UNICODE)."\n";
 echo 'heureOuverture='.$open.' heureFermeture='.$close."\n";
 echo 'nb paniers captés='.$items->count()."\n";
 echo 'somme total_remise='.$items->sum(fn($x)=>(float)($x->total_remise??0))."\n";
 echo 'somme créance compte_client/credit='.$credit."\n";
 echo "status:\n";foreach($all->groupBy($st) as $k=>$v)echo '  '.json_encode($k,JSON_UNESCAPED_UNICODE).': '.count($v)."\n";
 echo "modes_paiement:\n";if($pm)foreach($all->groupBy($pm) as $k=>$v)echo '  '.json_encode($k,JSON_UNESCAPED_UNICODE).': '.count($v)."\n";
 echo "echantillons:\n";foreach($items->take(10) as $x)echo json_encode($x,JSON_UNESCAPED_UNICODE)."\n";
}

