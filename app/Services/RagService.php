<?php
namespace App\Services;

use App\Models\KnowledgeChunk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class RagService
{
    public function retrieve(string $query,int $limit=4): Collection
    {
        $chunks=KnowledgeChunk::where('active',true)->get();
        if($chunks->isEmpty()) return collect();
        $queryEmbedding=$this->embedOne($query);
        if($queryEmbedding && $chunks->contains(fn($chunk)=>!empty($chunk->embedding))){
            return $chunks->filter(fn($chunk)=>!empty($chunk->embedding))->map(function($chunk)use($queryEmbedding){$chunk->score=$this->cosine($queryEmbedding,$chunk->embedding);return $chunk;})->sortByDesc('score')->take($limit)->values();
        }
        $words=collect(preg_split('/\s+/u',mb_strtolower($query)))->filter(fn($word)=>mb_strlen($word)>2)->unique();
        return $chunks->map(function($chunk)use($words){$text=mb_strtolower($chunk->title.' '.$chunk->content);$chunk->score=$words->sum(fn($word)=>mb_substr_count($text,$word));return $chunk;})->sortByDesc('score')->take($limit)->values();
    }

    public function indexAll(): int
    {
        $chunks=KnowledgeChunk::where('active',true)->whereNull('embedding')->get();
        if($chunks->isEmpty()) return 0;
        $indexed=0;
        foreach($chunks->chunk(100) as $batch){
            $batch=$batch->values();
            $response=Http::withToken(config('services.openai.api_key'))->timeout(120)->retry(3,1500)->post('https://api.openai.com/v1/embeddings',['model'=>config('services.openai.embedding_model'),'input'=>$batch->pluck('content')->all()])->throw();
            foreach($response->json('data',[]) as $item){if(isset($batch[$item['index']])){$batch[$item['index']]->update(['embedding'=>$item['embedding']]);$indexed++;}}
        }
        return $indexed;
    }

    private function embedOne(string $text): ?array
    {
        if(!config('services.openai.api_key')) return null;
        try{return Http::withToken(config('services.openai.api_key'))->timeout(30)->post('https://api.openai.com/v1/embeddings',['model'=>config('services.openai.embedding_model'),'input'=>$text])->throw()->json('data.0.embedding');}catch(\Throwable){return null;}
    }
    private function cosine(array $a,array $b): float{$dot=$na=$nb=0.0;foreach($a as $i=>$value){$other=$b[$i]??0;$dot+=$value*$other;$na+=$value*$value;$nb+=$other*$other;}return ($na&&$nb)?$dot/(sqrt($na)*sqrt($nb)):0;}
}
