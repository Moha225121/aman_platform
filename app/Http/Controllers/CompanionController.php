<?php
namespace App\Http\Controllers;

use App\Services\RagService;
use Illuminate\Http\{JsonResponse,Request};
use Illuminate\Support\Facades\{Http,Log};

class CompanionController extends Controller
{
    public function message(Request $request, RagService $rag): JsonResponse
    {
        abort_unless($request->user()->role === 'user', 403);
        $data=$request->validate([
            'message'=>'required|string|max:1000',
            'history'=>'nullable|array|max:10',
            'history.*.role'=>'required|in:user,assistant',
            'history.*.content'=>'required|string|max:1000',
        ]);

        if (!config('services.openai.api_key')) {
            return response()->json(['message'=>'لم تتم إضافة مفتاح OpenAI بعد. أضفه في ملف .env ثم أعد المحاولة.'],503);
        }

        $knowledge=$rag->retrieve($data['message']);
        $ragContext=$knowledge->map(fn($chunk)=>"[{$chunk->title} — {$chunk->source}]\n{$chunk->content}")->implode("\n\n");
        $history=collect($data['history'] ?? [])->take(-8)->map(fn($item)=>[
            'role'=>$item['role'],
            'content'=>[['type'=>$item['role']==='assistant'?'output_text':'input_text','text'=>$item['content']]],
        ])->values()->all();
        $history[]=['role'=>'user','content'=>[['type'=>'input_text','text'=>$data['message']]]];

        try {
            $response=Http::withToken(config('services.openai.api_key'))->acceptJson()->timeout(45)->post('https://api.openai.com/v1/responses',[
                'model'=>config('services.openai.model'),
                'instructions'=>$this->instructions($request,$ragContext),
                'input'=>$history,
                'reasoning'=>['effort'=>'low'],
                'text'=>['verbosity'=>'low'],
                'max_output_tokens'=>500,
                'store'=>false,
                'safety_identifier'=>hash_hmac('sha256',(string)$request->user()->id,config('app.key')),
            ]);

            if ($response->failed()) {
                Log::warning('OpenAI companion request failed',['status'=>$response->status(),'error'=>$response->json('error.message')]);
                return response()->json(['message'=>'تعذر الوصول إلى رفيق أمان الآن. حاول مرة أخرى بعد قليل.'],502);
            }

            $reply=$response->json('output_text');
            if (!$reply) {
                $reply=collect($response->json('output',[]))->flatMap(fn($item)=>$item['content']??[])->firstWhere('type','output_text')['text']??null;
            }
            return response()->json(['reply'=>$reply ?: 'أفهمك. أوصيك بمناقشة ما تشعر به مع مرشد مختص عبر جلسة آمنة.','sources'=>$knowledge->pluck('title')->values()]);
        } catch (\Throwable $exception) {
            Log::error('OpenAI companion exception',['message'=>$exception->getMessage()]);
            return response()->json(['message'=>'تعذر الاتصال برفيق أمان الآن. حاول مرة أخرى لاحقًا.'],502);
        }
    }

    private function instructions(Request $request,string $ragContext): string
    {
        $bookings=$request->user()->bookings()->get(['status','scheduled_at']);
        $context='لدى المستخدم '.$bookings->count().' طلب/جلسة، منها '.$bookings->where('status','pending')->count().' قيد المراجعة و'.$bookings->where('status','accepted')->count().' مقبولة.';
        return <<<PROMPT
أنت «رفيق أمان»، بوت إرشادي نفسي عربي داخل منصة أمان الليبية. دورك دعم أولي متعاطف، مساعدة المستخدم على وصف حالته، اقتراح خطوات يومية آمنة، وتشجيعه على حجز جلسة أو متابعة جلساته مع مرشد بشري. {$context}

قواعد إلزامية:
- أجب بالعربية الواضحة وباختصار ودفء، واسأل سؤال متابعة واحدًا عند الحاجة.
- لا تشخّص اضطرابًا، ولا تدّعي أنك طبيب أو معالج، ولا تصف أدوية أو تغيّر علاجًا.
- لا تقل إنك بديل عن المختص. عند الأعراض المستمرة أو المؤثرة، أوصِ بالجلسة والمتابعة المنتظمة.
- إذا ذكر المستخدم إيذاء النفس، الانتحار، إيذاء الآخرين، أو خطرًا فوريًا: اطلب منه فورًا التواصل مع خدمات الطوارئ المحلية أو التوجه لأقرب طوارئ، وألا يبقى وحده وأن يتواصل مع شخص موثوق. لا تواصل الاستبيان العادي.
- لا تطلب الاسم الحقيقي أو الهاتف أو العنوان أو أي بيانات تعريفية.
- لا تخترع موعدًا أو حالة حجز غير موجودة في السياق.

المعرفة المسترجعة من قاعدة أمان (استخدمها كأساس للإجابة ولا تخترع معلومات خارجها):
{$ragContext}
PROMPT;
    }
}
