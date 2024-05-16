<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $program, $first_name;

    /**
     * Create a new notification instance.
     */
    public function __construct($application)
    {
        $this->program    = $application['program'];
        $this->first_name = $application['first_name'];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirmation of Application Submission for '.$this->program)
            ->greeting('Hello '.$this->first_name.',')
            ->line('I trust this email finds you well. We write to formally acknowledge the submission of your recent application for '.$this->program.' at Flow Accelerator.')
            ->line('We extend our sincere gratitude for your interest in our program and for the meticulous attention you dedicated to completing the application process. Enclosed herewith, please find a copy of your submitted application for your records.')
            ->line('Please rest assured that your application is now queued for meticulous processing and review by our discerning admissions committee. Each application undergoes a comprehensive evaluation to ensure equitable consideration of all applicants.')
            ->line('Following the closure of the application window, our committee will embark on the thorough review process. Subsequently, you can anticipate receiving an email update concerning the status of your application. Should further information be necessary for our evaluation, we will promptly reach out to you using the contact details provided in your application.')
            ->line('Please note that this email is sent from a no-reply address, and you do not need to respond to it. Should you have any inquiries or require clarification at any juncture, please do not hesitate to contact us at via our social media platforms.')
            ->line('Once again, we express our gratitude for your interest in '.$this->program.' . We are honored by the opportunity to assess your candidacy and extend our best wishes for a favorable outcome in the application process.')
            ->line('Regards,')
            ->line('Flow Accelerator')
            ->line(' ')
            ->line('------------------------------------------------------')
            ->line(' ')
            ->line(new HtmlString('<p dir="rtl" style="text-align: right;">مرحبا '.$this->first_name.'،</p>'))
            ->line(new HtmlString('<p dir="rtl" style="text-align: right;">نأمل أن تكونوا بخير. نود أن نؤكد استلام طلبك الأخير للالتحاق ببرنامج  '.$this->program.' في مسرعة الأعمال فلو.</p>'))
            ->line(new HtmlString('<p dir="rtl" style="text-align: right;">نود أن نعبر عن شكرنا الصادق لاهتمامكم ببرنامجنا والجهد الدقيق الذي قمتم ببذله لاستكمال عملية التقديم. نرفق مع هذا البريد نسخة من طلبك لسجلاتكم.</p>'))
            ->line(new HtmlString('<p dir="rtl" style="text-align: right;">تأكدوا من أن طلبكم قد تم إدراجه الآن في قائمة الانتظار للمعالجة والاستعراض من قبل لجنة القبول الخاصة بنا. يخضع كل طلب لتقييم شامل لضمان النظرة العادلة لجميع المتقدمين.</p>'))
            ->line(new HtmlString('<p dir="rtl" style="text-align: right;">بعد انتهاء فترة التقديم، ستبدأ لجنتنا في عملية المراجعة. في وقت لاحق، يمكنكم توقع تلقي تحديث عبر البريد الإلكتروني بشأن حالة طلبكم. في حال الحاجة إلى مزيد من المعلومات لتقييمنا، سنقوم بالتواصل معكم بشكل سريع باستخدام معلومات الاتصال المقدمة في طلبكم.</p>'))
            ->line(new HtmlString('<p dir="rtl" style="text-align: right;">يرجى ملاحظة أن هذا البريد يُرسل من عنوان الإرسال الآلي ولا حاجة للرد عليه. في حال وجود أي استفسارات في أي وقت، لا تترددوا في التواصل معنا على عبر منصات التواصل الاجتماعي الخاصة بنا.</p>'))
            ->line(new HtmlString('<p dir="rtl" style="text-align: right;">مرة أخرى، نعبر عن شكرنا لاهتمامكم ببرنامج '.$this->program.' . نحن ممتنون لفرصة تقييمكم ونتمنى لكم كل التوفيق في عملية التقديم.</p>'))
            ->line(new HtmlString('<p dir="rtl" style="text-align: right;">مع خالص التحيات،</p>'))
            ->line(new HtmlString('<p dir="rtl" style="text-align: right;">مسرعة الأعمال فلو</p>'))
            ->salutation(' ');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
