-- Al Omran Blog CMS Seed Data
-- Default admin credentials: admin / admin (CHANGE IMMEDIATELY)

INSERT INTO users (name, email, password_hash, role) VALUES
('Admin', 'admin', '$2y$10$M4aTixw7i7yxPoD8VP5rr.MkJHdCqPHC2/BhHSBl6uRpEvkTXnqXq', 'admin');

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Al Omran Training & Development Center'),
('default_language', 'en'),
('supported_languages', 'en,ar'),
('default_index_status', 'index'),
('default_follow_status', 'follow'),
('default_og_image', ''),
('site_url', 'https://alomran.ae'),
('blog_path_en', '/en/blog'),
('blog_path_ar', '/blog');

INSERT INTO articles (status, author_id, published_at) VALUES
('published', 1, NOW()),
('draft', 1, NOW());

INSERT INTO article_translations (article_id, language, title, slug, excerpt, content, seo_title, meta_description, focus_keyword) VALUES
(1, 'en', 'Welcome to Al Omran Training Center', 'welcome-to-al-omran', 'Discover our comprehensive training programs.', '<h1>Welcome to Al Omran</h1><p>We offer world-class training programs in the UAE.</p>', 'Al Omran Training Center | Professional Training UAE', 'Al Omran Training Center offers certified professional training programs in the UAE.', 'training'),
(1, 'ar', 'مرحباً بكم في مركز العمران', 'مرحبا-بكم-في-مركز-العمران', 'اكتشف برامجنا التدريبية الشاملة.', '<h1>مرحباً بكم في مركز العمران</h1><p>نقدم برامج تدريبية عالمية المستوى في الإمارات.</p>', 'مركز العمران للتدريب | تدريب مهني في الإمارات', 'مركز العمران للتدريب يقدم برامج تدريبية معتمدة في الإمارات.', 'تدريب'),
(2, 'en', 'Getting Started with Digital Marketing', 'getting-started-digital-marketing', 'A beginner guide to digital marketing.', '<h1>Digital Marketing Guide</h1><p>Learn the basics of digital marketing.</p>', 'Digital Marketing Guide | Al Omran', 'Learn digital marketing basics with Al Omran Training Center.', 'digital marketing'),
(2, 'ar', 'البدء في التسويق الرقمي', 'البدء-في-التسويق-الرقمي', 'دليل المبتدئين في التسويق الرقمي.', '<h1>دليل التسويق الرقمي</h1><p>تعلم أساسيات التسويق الرقمي.</p>', 'دليل التسويق الرقمي | العمران', 'تعلم أساسيات التسويق الرقمي مع مركز العمران.', 'تسويق رقمي');

INSERT INTO careers (status, author_id, published_at) VALUES
('published', 1, NOW());

INSERT INTO career_translations (career_id, language, title, slug, location, employment_type, description, requirements, benefits) VALUES
(1, 'en', 'Training Coordinator', 'training-coordinator', 'Al Ain, UAE', 'full-time', '<p>We are looking for a Training Coordinator.</p>', '<ul><li>2+ years experience</li><li>Bachelor degree</li></ul>', '<ul><li>Competitive salary</li><li>Health insurance</li></ul>'),
(1, 'ar', 'منسق تدريب', 'منسق-تدريب', 'العين، الإمارات', 'full-time', '<p>نبحث عن منسق تدريب.</p>', '<ul><li>خبرة سنتين أو أكثر</li><li>درجة بكالوريوس</li></ul>', '<ul><li>راتب تنافسي</li><li>تأمين صحي</li></ul>');
