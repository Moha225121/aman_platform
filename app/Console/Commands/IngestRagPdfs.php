<?php
namespace App\Console\Commands;

use App\Models\KnowledgeChunk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class IngestRagPdfs extends Command
{
    protected $signature='rag:ingest-pdf {paths* : One or more PDF paths}';
    protected $description='Extract PDF pages and add page-aware chunks to the RAG knowledge base';

    public function handle(): int
    {
        foreach($this->argument('paths') as $path){
            $path=realpath($path) ?: $path;
            if(!is_file($path)){ $this->error("File not found: {$path}"); continue; }
            $this->info('Extracting '.basename($path).' ...');
            $process=new Process(['pdftotext','-enc','UTF-8','-layout',$path,'-']);
            $process->setTimeout(600);$process->run();
            if($process->isSuccessful()) $pages=preg_split('/\f/u',$process->getOutput());
            else {
                $this->warn('Poppler failed; using Unicode-compatible Python extraction.');
                $fallback=new Process([config('services.pdf_python'),base_path('scripts/extract_pdf_pages.py'),$path]);$fallback->setTimeout(1200);$fallback->run();
                if(!$fallback->isSuccessful()){ $this->error($fallback->getErrorOutput() ?: 'PDF extraction failed.'); continue; }
                $pages=json_decode($fallback->getOutput(),true);
                if(!is_array($pages)){ $this->error('PDF extractor returned invalid data.'); continue; }
            }
            $source=basename($path);$records=[];
            foreach($pages as $pageIndex=>$text){
                $text=$this->clean($text);if(mb_strlen($text)<120)continue;
                foreach($this->chunks($text) as $chunkIndex=>$content){
                    $records[]=['title'=>pathinfo($source,PATHINFO_FILENAME).' - صفحة '.($pageIndex+1).' - مقطع '.($chunkIndex+1),'content'=>$content,'source'=>$source.'#page='.($pageIndex+1),'active'=>true,'created_at'=>now(),'updated_at'=>now()];
                }
            }
            DB::transaction(function()use($source,$records){KnowledgeChunk::where('source','like',$source.'#page=%')->delete();foreach(array_chunk($records,300) as $batch)KnowledgeChunk::insert($batch);});
            $this->info('Added '.count($records).' chunks from '.count($pages).' pages.');
        }
        $this->newLine();$this->comment('Run php artisan rag:index to generate embeddings for new chunks.');
        return self::SUCCESS;
    }

    private function clean(string $text): string
    {
        $text=str_replace("\0",'',mb_convert_encoding($text,'UTF-8','UTF-8'));
        $lines=preg_split('/\R/u',$text);$lines=array_map(fn($line)=>trim(preg_replace('/[ \t]+/u',' ',$line)),$lines);
        return trim(preg_replace('/\s+/u',' ',implode(' ',$lines)));
    }
    private function chunks(string $text): array
    {
        $words=preg_split('/\s+/u',$text,-1,PREG_SPLIT_NO_EMPTY);$size=260;$overlap=35;$chunks=[];
        for($start=0;$start<count($words);$start+=($size-$overlap)){ $slice=array_slice($words,$start,$size);if(count($slice)<35)break;$chunks[]=implode(' ',$slice);if($start+$size>=count($words))break; }
        return $chunks;
    }
}
