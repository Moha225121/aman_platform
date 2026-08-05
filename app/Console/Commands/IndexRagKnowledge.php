<?php
namespace App\Console\Commands;

use App\Services\RagService;
use Illuminate\Console\Command;

class IndexRagKnowledge extends Command
{
    protected $signature='rag:index';
    protected $description='Generate OpenAI embeddings for the Aman guidance knowledge base';
    public function handle(RagService $rag): int
    {
        if(!config('services.openai.api_key')){$this->error('OPENAI_API_KEY is missing from .env');return self::FAILURE;}
        $this->info('Indexed '.$rag->indexAll().' knowledge chunks.');
        return self::SUCCESS;
    }
}
