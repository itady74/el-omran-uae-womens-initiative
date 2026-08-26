# Al Omran Training and Development
# UAE Women's Empowerment Initiative
## One Shot Landing Page Specification for Claude

## 1. PROJECT OBJECTIVE

Create a premium, professional, emotionally compelling Arabic RTL landing page for:

**مركز العمران للتدريب والتطوير**

The landing page promotes:

**مبادرة العمران لتمكين المرأة الإماراتية**

The initiative is launched on the occasion of Emirati Women's Day and provides:

- 5,000,000 AED total initiative value
- 500 Emirati women
- One full year of free English language education
- In person study through Al Omran Training and Development branches across the UAE

The design must communicate:

- Trust
- Education
- Empowerment
- UAE identity
- Professionalism
- Hope
- Opportunity
- Human impact
- Premium institutional quality

The website must feel like a high end UAE institutional campaign website.

It must not look like a generic NGO template, generic course landing page, or basic Bootstrap style website.

---

# 2. STRICT TECHNOLOGY REQUIREMENTS

Use only:

- HTML
- CSS

Do not use:

- JavaScript
- React
- Vue
- Angular
- Next.js
- TypeScript
- Tailwind CSS
- Bootstrap
- jQuery
- Any CSS framework
- Any JavaScript framework
- Any JavaScript dependency
- Any build system

The website must be deployable as a static website.

Recommended structure:

```text
/
├── index.html
├── styles.css
└── assets/
```

The final implementation must work by opening `index.html` directly in a browser.

---

# 3. ONE SHOT IMPLEMENTATION

Build the entire website in one shot.

Do not divide the implementation into phases.

Do not create Phase 1, Phase 2, Phase 3, milestones, or implementation stages.

Do not leave TODO comments.

Do not create unfinished placeholders except for content that is explicitly intended to be supplied later, such as the campaign video.

Do not ask for additional design decisions.

Make professional design decisions automatically whenever something is not explicitly specified.

The final result must be complete, polished, responsive, accessible, and ready for static deployment.

---

# 4. REQUIRED SKILLS

Follow the design and implementation principles from these skills:

```bash
npx skills add https://github.com/anthropics/skills --skill frontend-design
npx skills add https://github.com/wshobson/agents --skill responsive-design
npx skills add https://github.com/hugeicons/hugeicons --skill hugeicons
npx skills add https://github.com/itady74/ux-writing-arabic --skill ux-writing-arabic
npx skills add https://github.com/itady74/arabic-copywriter --skill arabic-copywriter
```

Apply the relevant principles from all five skills.

The explicit requirements in this document always take priority.

---

# 5. LOGO AND BRAND IDENTITY

The uploaded logo is the primary visual reference for the website.

Do not redesign the logo.

Do not distort the logo.

Do not change the logo proportions.

Do not recreate the logo using CSS.

Use the logo as the main visual identity anchor.

The visual system of the website must be derived from the logo.

The logo contains four primary brand colors:

- Blue
- Green
- Orange
- Yellow

The exact dominant colors were sampled directly from the supplied logo image.

---

# 6. EXACT LOGO COLOR PALETTE

Use these colors as the core brand palette.

```css
:root {
  --logo-blue: #406AB2;
  --logo-green: #60BB46;
  --logo-orange: #F4821F;
  --logo-yellow: #FFDE2D;
}
```

These are the primary sampled colors from the supplied logo:

```text
Blue   #406AB2
Green  #60BB46
Orange #F4821F
Yellow #FFDE2D
```

Create the website color system around these colors.

Recommended supporting palette:

```css
:root {
  --logo-blue: #406AB2;
  --logo-green: #60BB46;
  --logo-orange: #F4821F;
  --logo-yellow: #FFDE2D;

  --brand-blue-dark: #31558F;
  --brand-blue-deep: #183A70;

  --brand-green-dark: #438E32;
  --brand-orange-dark: #D96A12;
  --brand-yellow-dark: #D6B800;

  --white: #FFFFFF;
  --background: #F8FAFD;
  --surface: #FFFFFF;
  --surface-blue: #EFF4FB;
  --surface-green: #F1F8EE;
  --surface-orange: #FFF5EA;

  --text-primary: #17233B;
  --text-secondary: #5C6675;
  --text-muted: #7B8492;

  --border: #E2E8F0;
}
```

## Color usage

White must remain the dominant background.

Blue is the primary brand color.

Green represents growth, empowerment, and progress.

Orange and yellow are accent colors.

Do not use orange and yellow excessively.

Do not make the whole website blue.

Do not use neon colors.

Do not use random colors outside the defined system unless they are required for accessibility or form states.

Do not use dark mode.

---

# 7. TYPOGRAPHY

Use Google Fonts.

## Headings

Use:

**Alexandria**

Google Fonts:

https://fonts.google.com/specimen/Alexandria

Use Alexandria for:

- Hero headline
- Section headings
- Large statistics
- Major CTA text
- Important labels

## Body

Use:

**IBM Plex Sans Arabic**

Google Fonts:

https://fonts.google.com/specimen/IBM+Plex+Sans+Arabic

Use IBM Plex Sans Arabic for:

- Body copy
- Form labels
- Descriptions
- Supporting text
- Navigation
- Buttons where appropriate

The typography should feel:

- Modern
- Elegant
- Professional
- Arabic first
- Highly readable

Do not use decorative Arabic fonts.

Use appropriate font weights.

Do not make every heading extremely bold.

Use `clamp()` for responsive typography.

---

# 8. RTL REQUIREMENT

The entire website must be Arabic RTL.

Use:

```html
<html lang="ar" dir="rtl">
```

Ensure:

- Text direction is correct
- Form controls work correctly in RTL
- Icons are positioned correctly
- Arrows point in the correct visual direction
- Numbers remain readable
- English words remain readable when included
- Layout spacing works naturally in RTL

---

# 9. NO EMOJIS

Do not use emojis anywhere in the website.

This includes:

- Text content
- Headings
- Buttons
- Cards
- Form labels
- Navigation
- Footer
- Statistics
- HTML comments
- Accessibility labels
- Placeholder content

Do not use Unicode emoji characters.

Use professional icons instead.

---

# 10. ICON SYSTEM

Use icons from Hugeicons where appropriate.

Icons must be used instead of emojis.

Examples:

Education icon for the education benefit.

Communication icon for communication skills.

School or building icon for in person learning.

Check or verified icon for free tuition.

User icon for applicant information.

Location icon for branches.

Calendar icon for the study schedule.

Document icon for application information.

Arrow icon for CTA buttons.

Use icons consistently.

Do not use random icon styles from different libraries.

If Hugeicons cannot be loaded without violating the HTML and CSS only requirement, use inline SVG icons.

Inline SVG is allowed because it does not require JavaScript.

Do not add a JavaScript icon library.

Icons should be subtle and professional.

---

# 11. DESIGN DIRECTION

Core concept:

**Education creates opportunity.**

The visual story should connect:

**Woman → Education → English → Opportunity → Future → UAE**

The design should use:

- Generous whitespace
- Strong editorial typography
- Large hero typography
- Sophisticated cards
- Soft borders
- Controlled shadows
- Moderate border radius
- Premium spacing
- Strong visual hierarchy
- Elegant decorative elements

Avoid:

- Generic templates
- Excessive gradients
- Excessive shadows
- Excessive rounded containers
- Overcrowded layouts
- Too much photography
- Cheap looking effects
- Excessive animation
- Gaming style visuals
- Random colors

---

# 12. IMAGES FROM THE INTERNET

Use high quality real photography from the internet where photography improves the story.

Good subjects include:

- Emirati women
- Arab women studying
- Women learning English
- Professional women
- Women collaborating
- Training classrooms
- Education environments
- Professional development
- UAE related environments

Prefer reputable image sources such as:

- Unsplash
- Pexels
- Other reputable public image sources

Do not use obviously generic images if a better visual option is available.

Do not use images that misrepresent the initiative.

Photography should feel:

- Professional
- Natural
- Bright
- Aspirational
- Premium

Do not use photography in every section.

Use images strategically.

Use `loading="lazy"` for below the fold images.

All meaningful images must have descriptive `alt` text.

---

# 13. PAGE STRUCTURE

Build the page in this exact content order:

1. Header
2. Hero
3. Campaign statistics
4. Message from Al Omran
5. What we provide
6. Why we launched the initiative
7. Who can apply
8. Selection process
9. Study locations
10. Emotional CTA
11. Application form
12. Application information state
13. Final brand statement
14. Footer

---

# 14. HEADER

Create a premium institutional navigation.

Include:

- Al Omran logo
- عن المبادرة
- ماذا نقدم
- الشروط
- آلية الاختيار
- الفروع
- التقديم

Primary navigation CTA:

**قدمي طلبك الآن**

Use anchor links to relevant sections.

The header may be sticky.

The header must remain lightweight.

For mobile, do not require JavaScript.

Use a CSS based responsive navigation approach.

If a full mobile navigation cannot be implemented elegantly without JavaScript, use a compact mobile header with the primary CTA and a clean set of visible navigation links.

---

# 15. HERO

Create a powerful first screen.

Main heading:

**مبادرة العمران لتمكين المرأة الإماراتية**

Secondary headline:

**5 ملايين درهم لدعم تعليم المرأة الإماراتية**

Hero copy:

> بمناسبة يوم المرأة الإماراتية، يطلق مركز العمران للتدريب والتطوير مبادرة مجتمعية بقيمة 5,000,000 درهم، لتوفير فرصة تعليمية مجانية لـ 500 امرأة إماراتية لتعلم اللغة الإنجليزية لمدة عام كامل.

Supporting statement:

> لأن تمكين المرأة يبدأ بالعلم، ولأن طموح المرأة الإماراتية يستحق أن يجد من يدعمه.

Primary CTA:

**قدمي طلبك الآن**

Secondary CTA:

**اكتشفي تفاصيل المبادرة**

The secondary CTA should scroll to the initiative explanation.

The hero should contain the official logo.

Use a premium image composition featuring an Emirati or Arab woman in an educational or professional environment.

The hero should immediately communicate:

**A serious educational opportunity created for Emirati women.**

---

# 16. CAMPAIGN STATISTICS

Create a visually strong statistics section after the hero.

Four statistics:

### 5,000,000
درهم قيمة المبادرة

### 500
امرأة إماراتية

### سنة كاملة
من التعليم

### بدون رسوم
دراسة اللغة الإنجليزية

Use professional icons where useful.

Do not use emojis.

Make the statistics visually prominent.

---

# 17. MESSAGE FROM AL OMRAN

Heading:

**كلمة من مركز العمران**

Subheading:

**مبادرة نؤمن أن أثرها يتجاوز قاعة التدريب**

Create a premium video area.

Use a semantic video element if a real video becomes available:

```html
<video controls>
```

If no video file is available, create a polished static video placeholder.

The placeholder must clearly communicate where the campaign video will be placed.

Do not use JavaScript for video functionality.

Copy:

> رسالتنا بسيطة: نريد أن نمنح 500 امرأة إماراتية فرصة حقيقية لتطوير واحدة من أهم المهارات التي يمكن أن تفتح أمامها أبواباً جديدة في التعليم والعمل والتواصل والتطور الشخصي.

> هذه المبادرة هي مساهمة من مركز العمران في دعم وتمكين المرأة الإماراتية، واحتفاء بالدور الكبير الذي تقوم به في بناء حاضر ومستقبل دولة الإمارات.

---

# 18. WHAT WE PROVIDE

Heading:

**ماذا نقدم لكِ؟**

Subheading:

**سنة كاملة من تعلم اللغة الإنجليزية مجاناً**

Intro:

> من خلال المبادرة ستحصل كل مستفيدة يتم قبولها على:

Create four premium feature cards.

## Feature 01

Title:

**سنة دراسية كاملة**

Text:

> سنة دراسية كاملة لتعلم وتطوير مهارات اللغة الإنجليزية.

Use a professional education icon.

## Feature 02

Title:

**مهارات تواصل أقوى**

Text:

> تطوير مهارات التواصل من خلال التدريب على المحادثة والاستماع والقراءة والكتابة.

Use a professional communication icon.

## Feature 03

Title:

**دراسة حضورية**

Text:

> دراسة حضورية في أحد فروع مركز العمران للتدريب والتطوير في مختلف إمارات الدولة.

Use a professional building or school icon.

## Feature 04

Title:

**بدون رسوم دراسية**

Text:

> تتحمل المبادرة تكلفة البرنامج التعليمي للمستفيدات المقبولات.

Use a professional verified or education icon.

---

# 19. WHY WE LAUNCHED THE INITIATIVE

Heading:

**لماذا أطلقنا هذه المبادرة؟**

Copy:

> نؤمن في مركز العمران أن الاستثمار الحقيقي هو الاستثمار في الإنسان.

> والمرأة الإماراتية كانت ولا تزال شريكاً أساسياً في مسيرة التنمية والنجاح التي تعيشها دولة الإمارات.

> لذلك، وبمناسبة يوم المرأة الإماراتية، أردنا أن نقدم مبادرة يكون أثرها عملياً ومستداماً.

Create a visually powerful statistics composition:

**5 ملايين درهم**

في التعليم

**500 فرصة**

تعليمية حقيقية

**500 قصة نجاح**

محتملة

This should be one of the strongest visual sections on the page.

Use brand blue as the main visual anchor with controlled green, orange, and yellow accents.

---

# 20. WHO CAN APPLY

Heading:

**من يمكنها التقديم؟**

Intro:

> المبادرة مخصصة للمرأة الإماراتية التي تنطبق عليها الشروط التالية:

Create four requirement blocks.

Requirement 01:

**أن تكون المتقدمة امرأة إماراتية.**

Requirement 02:

**أن يكون عمرها 18 عاماً أو أكثر.**

Requirement 03:

**أن تكون متفرغة لمدة ساعة ونصف، ثلاثة أيام أسبوعياً.**

Requirement 04:

**أن تكون جادة وقادرة على الالتزام بالحضور والدوام طوال فترة البرنامج.**

Use check icons.

Closing copy:

> إذا كنتِ مستعدة لاستثمار وقتك في نفسك وتطوير مستقبلك، فهذه الفرصة لكِ.

CTA:

**أريد التقديم على المبادرة**

---

# 21. SELECTION PROCESS

Heading:

**كيف يتم اختيار المستفيدات؟**

Intro:

> حرصاً على تحقيق أكبر أثر ممكن، لن يتم قبول جميع الطلبات تلقائياً.

> بعد استقبال طلبات التقديم، سيتم اختيار جزء من المستفيدات وفقاً لـ:

Create a three step visual process.

## 01

**مطابقة شروط المبادرة**

> التأكد من استيفاء المتقدمة لجميع الشروط المطلوبة.

## 02

**أولوية التقديم**

> تُؤخذ أولوية التقديم بعين الاعتبار ضمن آلية اختيار المستفيدات.

## 03

**ترشيحات المؤسسات الداعمة للمرأة**

> سيتم استكمال العدد المستهدف من خلال ترشيحات المؤسسات الحكومية الداعمة للمرأة في مختلف إمارات الدولة، وفق آلية التنسيق والترشيح المعتمدة.

Final highlight:

> الهدف النهائي: الوصول إلى 500 امرأة إماراتية ومنحهن فرصة حقيقية لتطوير اللغة الإنجليزية لمدة عام كامل.

Use a timeline or numbered cards.

---

# 22. STUDY LOCATIONS

Heading:

**أين ستكون الدراسة؟**

Copy:

> تتوفر الدراسة من خلال فروع مركز العمران للتدريب والتطوير في مختلف إمارات دولة الإمارات العربية المتحدة.

> عند تقديم الطلب، يمكنك اختيار الإمارة والفرع الأنسب لكِ، وسيتم التواصل مع المتقدمات المقبولات لاستكمال إجراءات التسجيل وتحديد تفاصيل البرنامج.

Create a UAE focused visual section.

Show the seven emirates as clean selectable chips or cards:

- أبوظبي
- دبي
- الشارقة
- عجمان
- أم القيوين
- رأس الخيمة
- الفجيرة

Use a location icon.

Do not create an inaccurate geographic map.

If using a map image, verify its visual accuracy.

---

# 23. EMOTIONAL CTA

Create a strong full width CTA section.

Heading:

**هل أنتِ مستعدة لتبدئي؟**

Copy:

> قد تكون ساعة ونصف، ثلاثة أيام في الأسبوع، هي الوقت الذي تستثمرينه اليوم، لكن المهارة التي تكتسبينها قد ترافقك لسنوات قادمة.

> لا نعدكِ بأن اللغة الإنجليزية ستغير حياتكِ في يوم واحد.

> لكننا نعدكِ بفرصة حقيقية لتبدئي التغيير.

CTA:

**قدمي طلبك الآن**

The section should feel emotional, credible, and premium.

Do not make it overly sales focused.

---

# 24. APPLICATION FORM

Heading:

**قدمي طلبك للمشاركة**

Subheading:

**املئي البيانات التالية وسيتواصل معك فريق المبادرة**

Create a semantic HTML form.

## Field 01

Label:

**الاسم الكامل**

Type:

`text`

Required.

## Field 02

Label:

**رقم الهوية الإماراتية**

Type:

`text`

Required.

## Field 03

Label:

**رقم الهاتف**

Type:

`tel`

Required.

## Field 04

Label:

**البريد الإلكتروني**

Type:

`email`

Required.

## Field 05

Label:

**الإمارة**

Type:

`select`

Options:

- اختاري الإمارة
- أبوظبي
- دبي
- الشارقة
- عجمان
- أم القيوين
- رأس الخيمة
- الفجيرة

## Field 06

Label:

**الفرع المفضل للدراسة**

Type:

`select`

Default option:

**اختاري الإمارة أولاً**

Do not invent branch names or addresses.

## Field 07

Label:

**ما مستوى اللغة الإنجليزية لديكِ حالياً؟**

Options:

- اختاري المستوى
- مبتدئ
- متوسط
- جيد
- متقدم
- لا أعرف

## Field 08

Label:

**لماذا ترغبين في المشاركة في المبادرة؟**

Type:

`textarea`

Required.

## Field 09

Question:

**هل يمكنكِ الالتزام بالدراسة لمدة ساعة ونصف، ثلاثة أيام أسبوعياً؟**

Radio options:

- نعم
- لا

## Field 10

Question:

**هل أنتِ مستعدة للالتزام بالحضور والدوام طوال فترة البرنامج؟**

Radio options:

- نعم
- لا

Checkbox:

**أقر بأنني امرأة إماراتية وأبلغ من العمر 18 عاماً أو أكثر، وأن البيانات التي قدمتها صحيحة.**

Checkbox:

**أوافق على شروط وأحكام المبادرة وآلية اختيار المستفيدات.**

Submit button:

**إرسال طلب المشاركة**

---

# 25. FORM DESIGN

The form must feel trustworthy and premium.

Use:

- Clear labels
- Large touch targets
- Strong focus states
- Correct RTL alignment
- Good spacing
- Clear field grouping
- Required indicators where appropriate
- Accessible radio controls
- Accessible checkboxes
- Responsive layout
- Strong submit CTA

Desktop:

Use a two column layout where appropriate.

Mobile:

Use one column.

Do not use JavaScript validation.

Use native HTML attributes:

```html
required
type="email"
type="tel"
```

---

# 26. SUBMISSION STATE

Because the website must use HTML and CSS only, do not create fake backend functionality.

Do not claim that an application was successfully submitted without a backend.

Create a polished confirmation state or informational block that can later be connected to a real backend.

Heading:

**شكراً لاهتمامك بالمبادرة.**

Copy:

> سيقوم فريق مركز العمران بمراجعة الطلبات وفق شروط المبادرة وآلية الاختيار المعتمدة.

> في حال استيفاء الشروط وترشيحك للاستفادة، سيتواصل معك فريق المبادرة لاستكمال إجراءات القبول والتسجيل.

Important notice:

> يرجى العلم أن تعبئة نموذج التقديم لا تعني القبول النهائي في المبادرة، وأن عدد المقاعد محدود بـ500 مستفيدة.

---

# 27. FINAL BRAND STATEMENT

Create a strong closing section.

Large heading:

**5,000,000 درهم**

Secondary heading:

**استثمار في الإنسان، احتفاءً بالمرأة الإماراتية**

Brand name:

**مركز العمران للتدريب والتطوير**

Statement:

> نؤمن أن التعليم لا يمنحكِ مهارة فقط، بل يمنحكِ فرصة جديدة.

CTA:

**قدمي طلبك الآن**

Use the logo.

Use the four brand colors subtly.

---

# 28. FOOTER

Create a clean institutional footer.

Include:

- Logo
- مركز العمران للتدريب والتطوير
- المبادرة المجتمعية لتمكين المرأة الإماراتية

Navigation:

- عن المبادرة
- ماذا نقدم
- الشروط
- آلية الاختيار
- الفروع
- التقديم

Include placeholders for Privacy Policy and Terms if appropriate.

Do not invent:

- Phone numbers
- Email addresses
- Addresses
- Social media accounts
- Registration numbers
- Legal information

---

# 29. RESPONSIVE DESIGN

The page must be excellent at:

- 1440px and above
- 1280px
- 1024px
- 768px
- 480px
- 390px
- 360px

Mobile is a first class design requirement.

On mobile:

- Hero typography scales smoothly.
- Statistics become a clean grid.
- Cards stack naturally.
- Form becomes one column.
- CTA buttons can become full width.
- Images maintain correct aspect ratios.
- No horizontal scrolling.
- Navigation remains usable.
- Text remains readable.
- Decorative elements do not obstruct content.
- Spacing is reduced intelligently.
- Sections maintain strong hierarchy.

Use:

- CSS Grid
- Flexbox
- `clamp()`
- `min()`
- `max()`
- CSS custom properties
- Media queries

---

# 30. ACCESSIBILITY

Use semantic HTML5.

Include:

- `header`
- `nav`
- `main`
- `section`
- `article`
- `footer`

Use proper heading hierarchy.

Every form input must have a connected label.

Use descriptive image alt text.

Provide visible keyboard focus states.

Maintain strong color contrast.

Do not use color as the only indicator.

Use sufficiently large clickable areas.

Use ARIA only when useful and necessary.

Support:

```css
@media (prefers-reduced-motion: reduce)
```

---

# 31. CSS ONLY ANIMATIONS

Use subtle CSS animations only.

Possible effects:

- Gentle fade in
- Small upward movement
- Button hover transition
- Card hover elevation
- Image hover zoom
- Animated underline
- Subtle decorative movement

Do not over animate.

Do not create distracting effects.

Do not use JavaScript animation libraries.

Respect reduced motion preferences.

---

# 32. BRAND DECORATION

Use visual elements inspired by the logo:

- Circular arcs
- Curved lines
- Small geometric dots
- Stars as graphical SVG elements if appropriate
- Blue curves
- Green accent shapes
- Orange accent strokes
- Yellow accent details

Do not use emoji stars.

Do not reproduce the logo artwork as decorative elements.

The decorative system should feel like a sophisticated extension of the brand identity.

Use decorations sparingly.

---

# 33. UX WRITING

Arabic copy must feel natural and professional.

Use:

- Modern Arabic
- Clear UAE institutional tone
- Feminine addressing where appropriate
- Short readable paragraphs
- Clear CTAs
- Direct language

Avoid:

- Machine translated Arabic
- Excessive legal language
- Empty marketing phrases
- Repetition
- Corporate jargon
- Overly dramatic copy

Primary CTA:

**قدمي طلبك الآن**

Keep CTA wording consistent.

---

# 34. FACTUAL ACCURACY

Do not invent facts.

Do not add:

- Job guarantees
- Employment guarantees
- Certificates unless explicitly stated
- Transportation
- Accommodation
- Additional courses
- Additional benefits
- Guaranteed acceptance
- Exact branch addresses
- Application deadlines
- Contact details
- Government partnerships beyond the supplied content
- Eligibility requirements beyond the supplied requirements

Maintain these facts consistently:

**5,000,000 AED**

**500 Emirati women**

**One full year**

**Free English language education**

**In person study**

**Branches across the UAE**

---

# 35. CONTENT BALANCE

Do not make the website a wall of text.

Break content into:

- Hero
- Statistics
- Story
- Benefits
- Impact
- Eligibility
- Selection
- Locations
- CTA
- Form
- Closing statement

Use visual hierarchy to make the content easy to scan.

The visitor should understand the core opportunity within the first screen.

---

# 36. SEO

Add:

```html
<title>مبادرة العمران لتمكين المرأة الإماراتية | 500 فرصة تعليمية مجانية</title>
```

Meta description:

```text
مبادرة مجتمعية بقيمة 5 ملايين درهم لتوفير سنة كاملة من تعلم اللغة الإنجليزية مجاناً لـ500 امرأة إماراتية.
```

Include:

- `lang="ar"`
- Open Graph metadata
- Semantic HTML
- Descriptive headings

Do not over optimize.

---

# 37. PERFORMANCE

Keep the page lightweight.

Do not add unnecessary dependencies.

Use optimized image dimensions.

Use lazy loading for below the fold images.

Use:

```css
font-display: swap;
```

where applicable.

Avoid large background videos.

Avoid unnecessary third party scripts.

There must be no JavaScript.

---

# 38. CODE QUALITY

HTML must be:

- Semantic
- Clean
- Readable
- Well structured

CSS must be:

- Organized
- Component oriented
- Responsive
- Based on CSS variables
- Free from unnecessary duplication

Use section comments such as:

```css
/* ========================================
   HERO
======================================== */
```

Do not over-comment simple CSS.

---

# 39. FINAL QUALITY CHECK

Before finishing, verify all of the following:

- [ ] Entire website is Arabic RTL
- [ ] `lang="ar"` exists
- [ ] `dir="rtl"` exists
- [ ] White is the primary background
- [ ] Logo is respected
- [ ] Exact logo blue `#406AB2` is used
- [ ] Exact logo green `#60BB46` is used
- [ ] Exact logo orange `#F4821F` is used
- [ ] Exact logo yellow `#FFDE2D` is used
- [ ] Alexandria is used for headings
- [ ] IBM Plex Sans Arabic is used for body text
- [ ] No emojis exist anywhere
- [ ] Icons are used instead of emojis
- [ ] Hugeicons principles are followed
- [ ] No JavaScript exists
- [ ] No React exists
- [ ] No Tailwind exists
- [ ] No Bootstrap exists
- [ ] All requested sections exist
- [ ] All requested copy exists
- [ ] All requested form fields exist
- [ ] All seven UAE emirates exist in the selector
- [ ] No invented facts exist
- [ ] Images come from appropriate internet sources
- [ ] Images have alt text
- [ ] CTA buttons link to the application section
- [ ] Form is semantic
- [ ] Form fields are accessible
- [ ] Responsive design works on mobile
- [ ] No horizontal overflow
- [ ] Keyboard focus states exist
- [ ] Reduced motion is supported
- [ ] CSS animations are subtle
- [ ] Typography is readable
- [ ] Visual hierarchy is premium
- [ ] Website does not look like a generic template

---

# 40. MOST IMPORTANT DESIGN GOAL

The visitor should feel:

**هذه فرصة حقيقية صممت من أجلي، وتستحق أن أتقدم لها.**

An institutional stakeholder should feel:

**هذه مبادرة إماراتية موثوقة، احترافية، ومدروسة بعناية.**

The design must balance:

**Emotion + Credibility + Clarity + Premium Visual Design**

Do not sacrifice clarity for aesthetics.

Do not sacrifice accessibility for visual effects.

Do not sacrifice credibility for marketing language.

---

# 41. FINAL IMPLEMENTATION COMMAND

Implement the complete website now in one shot.

Do not ask me to choose between multiple design directions.

Do not provide multiple versions.

Do not stop after creating a skeleton.

Do not leave TODOs.

Do not divide the implementation into phases.

Do not add JavaScript.

Do not add a framework.

Do not use emojis.

Use professional icons.

Use the exact logo colors:

```text
#406AB2
#60BB46
#F4821F
#FFDE2D
```

Use:

```text
Alexandria
IBM Plex Sans Arabic
```

Use real internet photography where appropriate.

Build the complete polished responsive Arabic RTL landing page using HTML and CSS only.

The result must be ready for static deployment.
