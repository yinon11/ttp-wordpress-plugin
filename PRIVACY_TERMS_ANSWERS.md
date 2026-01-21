# תשובות לשאלות על מדיניות הפרטיות והתקנון

## מדיניות הפרטיות - תשובות לשאלות

### 1. זרימת המידע - איחסון הגדרות סוכן AI

**שאלה:** כשמשתמש יוצר סוכן AI ומגדיר לו הנחיות, היכן המידע הזה מאוחסן?

**תשובה:** 
- ✅ **המידע מאוחסן בשרתים שלך** (לא בקנדה כפי שצוין קודם)
- הגדרות הסוכן (system prompt, first message, voice settings, וכו') נשלחות דרך API ל-backend ונשמרות בשרתים שלך
- תוכן האתר (pages, posts, products) נשלח גם הוא ל-backend בעת יצירת הסוכן האוטומטית ונשמר בשרתים שלך
- **לעדכן במדיניות הפרטיות:** לציין היכן בדיוק השרתים (מיקום גיאוגרפי), מה משך השמירה, ומה מדיניות הגיבוי

### 2. העברת מידע לספקי AI

**שאלה:** האם המידע מועבר לספקי ה-AI רק ברגע השיחה או נשמר אצלם באופן קבוע?

**תשובה:**
- ✅ **המידע נשמר אצלך בשרתים באופן קבוע** ומועבר ל-AI רק בעת הצורך (בזמן השיחה)
- הגדרות הסוכן (system prompt) נשמרות בשרתים שלך ונשלחות ל-OpenAI רק בזמן השיחה
- תוכן השיחות נשלח ל-OpenAI בזמן השיחה - **צריך לבדוק אם OpenAI שומר את זה** (זה תלוי בהגדרות שלהם)
- **לעדכן במדיניות הפרטיות:** להבהיר שהמידע נשמר בשרתים שלך, ומועבר ל-AI רק בעת השיחה
- **חשוב:** לבדוק אם יש opt-out מאימון מודלים ב-OpenAI ולהזכיר את זה במדיניות הפרטיות

### 3. הקלטות קול

**שאלה:** מה עם הקלטות הקול, הן נשמרות רק אצלך בקנדה או מועברות גם ל-ElevenLabs לעיבוד?

**תשובה:**
- ✅ **לא קשור ל-ElevenLabs - הם לא מקבלים מאיתנו כלום**
- ✅ **ההקלטות נשמרות אצלך בשרתים שלך** ומאוחסנות ב-S3
- הקלטות הקול נשלחות ל-`speech.talktopc.com` לעיבוד, אבל לא ל-ElevenLabs
- **לעדכן במדיניות הפרטיות:** להבהיר שהקלטות נשמרות בשרתים שלך ב-S3, ולא מועברות ל-ElevenLabs

### 4. טרנסקריפציה

**שאלה:** מי מבצע את הטרנסקריפציה של השיחות?

**תשובה:**
- ✅ **אתה משתמש במודל STT משלך** (Speech-to-Text)
- ✅ **אתה אוסף את כל מה שנאמר מהלקוח** ואת המידע שמיוצר על ידי ה-LLM, ויחד מקבלים טרנסקריפט
- ✅ **הטרנסקריפט נשמר אצלך בשרתים שלך**
- **לעדכן במדיניות הפרטיות:** להבהיר שאתה מבצע את הטרנסקריפציה בעצמך באמצעות מודל STT, והטרנסקריפט נשמר בשרתים שלך

### 5. איסוף מידע על מתקשרים

**שאלה:** כשסוכן AI מנהל שיחה עם מישהו שמתקשר, האם אתה אוסף מידע על אותו מתקשר מעבר לשיחה עצמה, כמו מספר טלפון או מיקום גיאוגרפי?

**תשובה:**
- ✅ **כן, אתה אוסף את מספר הטלפון** של המתקשר
- ✅ **לא אוסף מיקום גיאוגרפי**
- **לעדכן במדיניות הפרטיות:** להבהיר בדיוק מה נאסף:
  - מספר טלפון (נאסף)
  - תוכן השיחה (נאסף)
  - IP address? (כנראה כן, דרך שרתי ה-backend - צריך לבדוק)
  - User-Agent? (כנראה כן - צריך לבדוק)
  - מיקום גיאוגרפי (לא נאסף)

### 6. Telnyx - היקף המידע

**שאלה:** לגבי Telnyx, מה היקף המידע שמועבר אליהם? רק מספרי טלפון ומטא-דאטה של שיחות, או גם תוכן השיחות?

**תשובה:**
- ✅ **הפוך - מספרי הטלפון מגיעים מ-Telnyx** (לא נשלחים אליהם)
- ✅ **כל שיחה טלפונית עוברת דרך Telnyx**, כך שהם חשופים לכל המידע הקולי העובר בשיחה
- **חשוב מאוד:** זה אומר ש-Telnyx חשופים לתוכן השיחות (audio), לא רק מטא-דאטה
- **לעדכן במדיניות הפרטיות:** להבהיר ששיחות טלפוניות עוברות דרך Telnyx, ולכן הם חשופים לתוכן הקולי של השיחות
- **חשוב:** לבדוק את מדיניות הפרטיות של Telnyx ולוודא שיש DPA איתם (ראה סעיף 9)

### 7. DPA עם OpenAI

**שאלה:** האם חתמת בפועל על ה-DPA עם OpenAI? זה דורש מילוי טופס באתר שלהם.

**תשובה:**
- ❌ **לא חתמת על DPA עם OpenAI**
- **דחוף מאוד:** לחתום על DPA מיד דרך: https://openai.com/enterprise-privacy/
- זה חשוב במיוחד כי אתה מעביר מידע אישי ל-OpenAI (תוכן שיחות, system prompts)
- **לעדכן במדיניות הפרטיות:** לאחר החתימה, להזכיר שיש DPA עם OpenAI

### 8. Opt-out מאימון מודלים ב-ElevenLabs

**שאלה:** האם הפעלת את אפשרות ה-opt-out מאימון מודלים אצל ElevenLabs?

**תשובה:**
- ✅ **לא רלוונטי - אתה לא משתמש ב-ElevenLabs**
- אין צורך לטפל בזה

### 9. DPA עם Telnyx

**שאלה:** האם ביקשת את ה-DPA מ-Telnyx?

**תשובה:**
- ❌ **לא ביקשת DPA מ-Telnyx**
- **דחוף מאוד:** לבקש DPA מ-Telnyx מיד
- זה חשוב במיוחד כי כל השיחות הטלפוניות עוברות דרך Telnyx, והם חשופים לתוכן הקולי של השיחות
- ללא DPA, אתה חשוף לסיכונים משפטיים לפי GDPR

### 10. הסמכות אבטחה

**שאלה:** יש לך הסמכות כמו SOC 2 או ISO 27001? יש לך תהליך מסודר לזיהוי פריצות ואירועי אבטחה?

**תשובה:**
- ❌ **עדיין אין לך הסמכות אבטחה** (SOC 2, ISO 27001)
- **מומלץ:** 
  - לשקול להשיג הסמכות (בעיקר SOC 2 Type 2 אם יש לקוחות גדולים)
  - ליצור תהליך מסודר לזיהוי ואירועי אבטחה (incident response plan)
  - להזכיר במדיניות הפרטיות את אמצעי האבטחה שיש לך (אפילו בלי הסמכות רשמית)

### 11. מנגנון הסכמה להקלטות

**שאלה:** האם הפלטפורמה מספקת כלים לזה, כמו הודעה אוטומטית בתחילת השיחה? יש מנגנון שמאפשר למתקשר לבקש שלא להיות מוקלט?

**תשובה:**
- ✅ **אין זה באחריות הלקוח המשתמש** - זה באחריות המשתמש העסקי שמפעיל את הסוכן
- **לעדכן בתקנון:** להבהיר שהמשתמש העסקי אחראי להודיע למתקשרים על ההקלטה ולקבל הסכמה (ראה סעיף 5.3 בתקנון)
- **מומלץ:** לשקול להוסיף כלים בפלטפורמה שיעזרו למשתמשים העסקיים:
  - תבנית הודעה אוטומטית בתחילת השיחה: "שיחה זו מוקלטת למטרות [X, Y, Z]"
  - אפשרות למתקשר לבקש שלא להיות מוקלט (למשל לחיצה על מקש)
  - מנגנון טכני שמכבד את הבקשה

### 12. הסכמה לעוגיות

**שאלה:** אתה משתמש ב-Google Analytics, Microsoft Clarity, Yandex Analytics ו-Facebook Analytics. יש לך מנגנון הסכמה מפורשת לעוגיות? באנר עוגיות באתר?

**תשובה:**
- ✅ **כן, יש לך מנגנון הסכמה לעוגיות** באתר הראשי
- זה טוב ומתאים לדרישות GDPR
- **לבדוק:** לוודא שהמנגנון מכסה את כל העוגיות (Google Analytics, Microsoft Clarity, Yandex Analytics, Facebook Analytics)

### 13. תהליך טיפול בבקשות גישה/מחיקה

**שאלה:** מאחר ואין לך עדיין תהליך מסודר לטיפול בבקשות גישה או מחיקה, נצטרך לבנות לך אחד.

**תשובה:**
- ❌ **נכון - אין לך עדיין תהליך מסודר**
- **דחוף:** ליצור תהליך מסודר:
  - טופס מקוון לבקשות גישה/מחיקה (להציב באתר או במדיניות הפרטיות)
  - תהליך פנימי לטיפול בבקשות (תוך 30 יום לפי GDPR)
  - מנגנון אימות זהות לפני ביצוע הבקשה
  - תיעוד של כל הבקשות
  - להזכיר את התהליך במדיניות הפרטיות

---

## התקנון - תשובות לשאלות

### 1. סתירה בין מקום ההתאגדות לדין החל

**שאלה:** החברה רשומה בדלאוור בארה״ב, אבל בסעיף 12.1 נקבע שהדין החל הוא דין מדינת ישראל.

**תשובה:**
- ✅ **שינית את התקנון לחברה הישראלית** - אז הכתובת ליישוב סכסוך נשארת ישראל
- **לעדכן:** לוודא שהתקנון מעודכן ומתייחס לחברה הישראלית, לא לחברה האמריקאית
- **אם עדיין יש התייחסות לדלאוור:** להסיר או לעדכן בהתאם

### 2. הגדרות חסרות

**שאלה:** חסרות הגדרות קריטיות: End User, Caller, Personal Data, Transcript.

**תשובה:**
- **מומלץ:** להוסיף סעיף הגדרות:

```
1. DEFINITIONS

1.1 "Agent" means an AI-powered conversational agent created and configured by you through the Service.

1.2 "Caller" or "End User" means any individual who interacts with your Agent, including but not limited to visitors to your website or individuals who call your Agent via telephone.

1.3 "Content" means all data, information, text, audio, video, and other materials that you upload, transmit, or otherwise make available through the Service, including but not limited to Agent configurations, system prompts, site content, and conversation transcripts.

1.4 "Personal Data" means any information relating to an identified or identifiable natural person, as defined under applicable data protection laws including the GDPR.

1.5 "Transcript" means a written record of a conversation between a Caller and an Agent, generated through speech-to-text transcription services.

1.6 "Service" means the TalkToPC platform, including all features, functionality, and services provided through the website and API.
```

### 3. מדיניות החזרים

**שאלה:** סעיף 4.5 קובע "All sales are final" - זה בעייתי לפי חוק הגנת הצרכן בישראל ו-Consumer Rights Directive באירופה.

**תשובה:**
- **בעיה:** לא תואם חוק
- **פתרון מוצע:** להוסיף חריגים:

```
4.5 Refund Policy

All sales are final, except where required by applicable law. Consumers residing in Israel have the right to cancel a transaction within 14 business days of purchase in accordance with the Consumer Protection Law. Consumers residing in the European Union or European Economic Area have the right to withdraw from a contract within 14 days of conclusion in accordance with the Consumer Rights Directive. To exercise your right of withdrawal, please contact us at support@talktopc.com within the applicable time period.
```

### 4. חוסר הגנה על משתמשי קצה

**שאלה:** אין התייחסות לזכויות של האנשים שמתקשרים לסוכני ה-AI.

**תשובה:**
- **מומלץ:** להוסיף סעיף:

```
5.3 End User Compliance

You acknowledge that TTP GO provides a B2B platform and has no direct relationship with the end users who interact with your AI Agents. You are solely responsible for: (a) providing end users with appropriate privacy notices regarding data collection and processing; (b) obtaining any consents required under applicable law, including GDPR where applicable; (c) disclosing to end users that they are interacting with an AI system where required by law, including under the EU AI Act for Users operating in the European Union; and (d) ensuring your use of the Service complies with all applicable laws regarding your end users. TTP GO shall have no liability arising from your failure to comply with these obligations.
```

### 5. סעיפים חסרים

**שאלה:** אין סעיפים: Force Majeure, הפניה למדיניות הפרטיות, SLA, עדכון מחירים, ייצוא מידע, מנגנון תלונות.

**תשובה:**
- **מומלץ:** להוסיף את כל הסעיפים הבאים:

#### Force Majeure

```
14. FORCE MAJEURE

TTP GO shall not be liable for any failure or delay in performing its obligations under these Terms where such failure or delay results from circumstances beyond its reasonable control, including but not limited to: acts of God, natural disasters, war, terrorism, riots, embargoes, acts of civil or military authorities, fire, floods, epidemics, strikes, failures or delays of third-party service providers (including AI service providers and telecommunications carriers), internet or power outages, or cyberattacks. During any such event, TTP GO's obligations shall be suspended for the duration of the event, and TTP GO shall use reasonable efforts to resume performance as soon as practicable. If such event continues for more than 30 consecutive days, either party may terminate the affected Services without penalty upon written notice.
```

#### הפניה למדיניות הפרטיות

```
15. PRIVACY POLICY

Your use of the Service is also governed by our Privacy Policy, available at https://talktopc.com/privacy. The Privacy Policy describes how we collect, use, store, and share your personal data and Content. By using the Service, you acknowledge that you have read and understood the Privacy Policy. In the event of any conflict between these Terms and the Privacy Policy regarding data protection matters, the Privacy Policy shall prevail. We encourage you to review the Privacy Policy regularly, as it may be updated from time to time in accordance with its terms.
```

#### SLA

```
16. SERVICE AVAILABILITY

16.1 Availability Target. TTP GO strives to maintain Service availability of 99.5% measured on a monthly basis, excluding scheduled maintenance and circumstances beyond our reasonable control as described in Section 14.

16.2 Scheduled Maintenance. TTP GO may perform scheduled maintenance that temporarily affects Service availability. Where practicable, we will provide at least 48 hours advance notice of scheduled maintenance via email or through the Service dashboard.

16.3 Service Disruptions. In the event of unscheduled service disruptions, TTP GO will use commercially reasonable efforts to restore Service functionality as quickly as possible and will provide status updates through our status page at status.talktopc.com or via email.
```

#### עדכון מחירים

```
17. PRICING CHANGES

17.1 Right to Modify Pricing. TTP GO reserves the right to modify its pricing, including Credit costs and subscription fees, at any time.

17.2 Advance Notice. We will provide at least 30 days advance notice of any pricing changes via email to the address associated with your account. For annual subscription holders, price changes will take effect at the start of the next renewal period.

17.3 Your Options. If you do not agree to a pricing change, you may cancel your subscription before the new pricing takes effect. Continued use of the Service after the effective date of a pricing change constitutes acceptance of the new pricing.

17.4 Existing Credits. Pricing changes shall not affect Credits already purchased prior to the effective date of the change.
```

#### ייצוא מידע

```
18. DATA EXPORT AND PORTABILITY

18.1 During Active Subscription. During your active subscription, you may export your data, including Agent configurations, conversation transcripts (where recording is enabled), and account information, through the Service dashboard or by submitting a request to support@talktopc.com.

18.2 Upon Termination. Following account termination or subscription cancellation, you will have 7 days to request an export of your data. After this period, your data will be deleted in accordance with our Privacy Policy and data retention schedules.

18.3 Export Format. Data exports will be provided in commonly used, machine-readable formats such as JSON or CSV where technically feasible.

18.4 Export Fees. Standard data exports are provided free of charge. TTP GO reserves the right to charge reasonable fees for custom export requests or exports requiring significant manual processing.
```

#### מנגנון תלונות

```
19. COMPLAINTS AND DISPUTE RESOLUTION PROCESS

19.1 Initial Contact. If you have a complaint regarding the Service, please contact us at support@talktopc.com with a detailed description of your concern. We will acknowledge receipt of your complaint within 7 business days.

19.2 Investigation and Response. We will investigate your complaint and provide a substantive response within 14 business days. If additional time is required, we will notify you of the expected timeline.
```

#### הגנת מידע

```
20. DATA PROTECTION

20.1 Your use of the Service is subject to our Privacy Policy at https://talktopc.com/privacy.

20.2 Where you process personal data of your end users through the Service, you act as the Data Controller and TTP GO acts as the Data Processor. You are solely responsible for complying with applicable data protection laws, including providing privacy notices to your end users and responding to their data subject requests.

20.3 Upon request, TTP GO will enter into a Data Processing Agreement with customers who require one for GDPR compliance.

20.4 You agree to indemnify TTP GO from any claims arising from your failure to comply with applicable data protection laws.
```

---

## סיכום - פעולות נדרשות

### דחוף מאוד (לפני פרסום/עדכון המדיניות):

1. ❌ **לחתום על DPA עם OpenAI** - זה דחוף מאוד כי אתה מעביר מידע אישי ל-OpenAI
   - דרך: https://openai.com/enterprise-privacy/

2. ❌ **לבקש DPA מ-Telnyx** - זה דחוף מאוד כי כל השיחות הטלפוניות עוברות דרך Telnyx
   - הם חשופים לתוכן הקולי של השיחות

3. ❌ **ליצור תהליך מסודר לטיפול בבקשות גישה/מחיקה**
   - טופס מקוון
   - תהליך פנימי (30 יום)
   - מנגנון אימות זהות

4. ✅ **לעדכן במדיניות הפרטיות:**
   - היכן בדיוק מאוחסן המידע (בשרתים שלך, לא בקנדה)
   - מה נשלח ל-OpenAI ומתי (רק בעת השיחה, נשמר אצלך)
   - מה נשלח ל-Telnyx (כל השיחות הטלפוניות עוברות דרכם)
   - מי מבצע טרנסקריפציה (אתה באמצעות מודל STT משלך)
   - מה נאסף על מתקשרים (מספר טלפון, לא מיקום גיאוגרפי)
   - איפה הקלטות נשמרות (בשרתים שלך ב-S3)

### חשוב (לעדכן בתקנון):

1. ✅ **לוודא שהתקנון מעודכן לחברה הישראלית** (לא דלאוור)
2. ✅ להוסיף הגדרות חסרות (End User, Caller, Personal Data, Transcript)
3. ✅ לתקן את מדיניות ההחזרים (להוסיף חריגים לחוק הגנת הצרכן)
4. ✅ להוסיף סעיף אחריות על משתמשי קצה (5.3 End User Compliance)
5. ✅ להוסיף את כל הסעיפים החסרים (Force Majeure, SLA, עדכון מחירים, ייצוא מידע, מנגנון תלונות, הגנת מידע)

### מומלץ (לא דחוף):

1. לשקול להשיג הסמכות אבטחה (SOC 2, ISO 27001)
2. ליצור תהליך מסודר לזיהוי פריצות ואירועי אבטחה
3. לשקול להוסיף כלים בפלטפורמה שיעזרו למשתמשים העסקיים עם הסכמה להקלטות (תבנית הודעה, מנגנון opt-out)
