<?php

declare(strict_types=1);

namespace Database\Providers;

use Faker\Provider\Base;

class CompanyProvider extends Base
{
    /* cspell:disable */
    protected static array $divisions = [
        'Entertainment', 'Private Equity', 'Sales', 'Headquarters', 'Katcon',
        'Accenture', 'Cintermex', 'Diestel', 'Fibra Inn', 'Gruma',
        'International', 'LEGO', 'Electronics', 'Praxis', 'Pyesca', 'Finance',
        'Biotechnology', 'London Heathrow', 'Consumer', 'Real Assets',
    ];

    protected static array $industries = [
        'Consumer Goods & Services', 'Financial Services', 'Life Sciences', 'Industrial', 'Not For Profit',
        'Professional Services', 'Technology & Media', 'Beverages', 'Food Producers', 'Retail', 'Household Goods',
        'Leisure Products', 'Personal Goods', 'Tobacco', 'Travel & Leisure', 'Equity Investment Instruments',
        'Life Insurance', 'Non-life Insurance', 'Real Estate Investment & Services', 'Healthcare Equipment & Services',
        'Aerospace & Defense', 'Alternative Energy', 'Automobiles & Parts', 'Chemicals', 'Construction & Materials',
        'Electricity', 'Electronic & Electrical Equipment', 'Forestry & Paper', 'Gas, Water & Multi-Utilities',
        'General Industrials', 'Industrial Engineering', 'Industrial Metals & Mining', 'Industrial Transportation',
        'Mining', 'Oil & Gas Producers', 'Support Services', 'Energy & Utilities', 'Charities', 'Public Services',
        'Social Care', 'Business Support Services', 'Business Training & Employment Agencies',
        'Financial Administration', 'Legal Services', 'Management Consulting', 'Media & Publishing',
        'Software & Computer Services', 'Technology Hardware & Equipment', 'Telecommunications', 'Airlines',
        'Fashion & Apparel Retailers', 'Brewers', 'Broadline Retailers', 'Clothing & Accessories',
        'Consumer Electronics', 'Distillers & Vintners', 'Drug Retailers', 'Durable Household Products',
        'Food Retailers & Wholesalers', 'Footwear', 'Furnishings', 'Gambling', 'Home Improvement Retailers', 'Hotels',
        'Nondurable Household Products', 'Personal Products', 'Recreational Products', 'Recreational Services',
        'Restaurants & Bars', 'Soft Drinks', 'Specialized Consumer Services', 'Specialty Retailers', 'Toys',
        'Asset Management', 'Consumer Finance', 'Full Line Insurance', 'Insurance Brokers', 'Investment Services',
        'Consumer Mortgage Finance', 'Nonequity Investment Instruments', 'General Insurance', 'Reinsurance',
        'Specialty Finance', 'Biotechnology', 'Healthcare Providers', 'Medical Equipment', 'Medical Supplies',
        'Pharmaceuticals', 'Mobile Apps', 'Broadcasting & Entertainment', 'Computer Hardware', 'Computer Services',
        'Electronic Office Equipment', 'Fixed Line Telecommunications', 'Internet', 'Media Agencies',
        'Mobile Telecommunications', 'Online', 'Publishing', 'Semiconductors', 'Recruitment',
        'Oil Equipment, Services & Distribution', 'Pharmaceuticals & Biotechnology', 'Food & Drug Retailers',
        'Real Estate Investment Trusts', 'Exploration & Production', 'Integrated Oil & Gas', 'Oil Equipment & Services',
        'Pipelines', 'Renewable Energy Equipment', 'Alternative Fuels', 'Commodity Chemicals', 'Specialty Chemicals',
        'Forestry', 'Paper', 'Aluminum', 'Nonferrous Metal Production', 'Iron & Steel', 'Coal', 'Diamonds & Gemstones',
        'General Mining', 'Gold Mining', 'Platinum & Precious Metals', 'Building Materials & Fixtures',
        'Heavy Construction', 'Aerospace', 'Defense', 'Containers & Packaging', 'Diversified Industrials',
        'Commercial Vehicles & Trucks', 'Industrial Machinery', 'Delivery Services', 'Marine Transportation',
        'Railroads', 'Transportation Services', 'Trucking', 'Industrial Suppliers', 'Waste & Disposal Services',
        'Farming, Fishing & Agriculture', 'Food Products', 'Home Construction', 'Travel & Tourism',
        'Conventional Electricity', 'Alternative Electricity', 'Gas Distribution', 'Multi-Utilities',
        'Water Management', 'Real Estate Holding & Development', 'Real Estate Services', 'Software',
        'Telecommunications Equipment', 'Advanced Materials', 'Architecture & Construction', 'Agriculture',
        'Engineering', 'Automotive', 'Aviation', 'Banking', 'Capital Equipment', 'Capital Markets', 'Hospitality',
        'Education', 'Venture Capital & Private Equity', 'Video Games', 'Non-Governmental Organisation', 'Energy',
        'Philanthropy', 'Political Organisation', 'Printing', 'Security Services', 'Investment Banking',
        'Corporate Finance', 'Manufacturing', 'Discrete Manufacturing', 'Process Manufacturing',
        'Mechanical & Plant Engineering', 'Automation Technology', 'Mechatronics', 'Audit & Tax', 'Textile Engineering',
        'Public Transportation', 'Medical Technology', 'Business Process Outsourcing', 'IT Outsourcing',
        'Cloud Computing', 'Ecommerce', 'Food Manufacturer', 'Food Processing', 'Consumer Healthcare Retailers',
        'Mixed Retail (e.g. Supermarkets)', 'Petcare Retailers', 'Home & Garden Retailers', 'White Good Retailers',
        'Clothing & Accessories Retailers', 'Sports Goods Retailing', 'Digital Retailing',
        'Automobile & Motorcycle Retailing', 'Furniture', 'Garden Centres', 'Pet Care', 'Health Clubs & Gyms',
        'Cinema & Night Clubs', 'Menswear', 'Womenswear', 'Childrenswear', 'Accessories', 'Consumer Products',
        'Sports Products', 'Musical Instruments', 'Games & Toys (Exc. Electronics)', 'Hard Copy Publishing',
        'Newspapers & Magazines', 'Book Publishing', 'Electronic Publishing', 'Publishing Software',
        'Entertainment Media', 'Film & Video', 'Television & Radio', 'Music', 'Advertising Agencies', 'Luxury Goods',
        'Consumer Care Products', 'Beauty', 'Consumer Healthcare', 'Household Care Products',
        'OEM Parts (Original Equipment Manufacturing)', 'Aftermarket', 'Consumer Chemicals', 'Plastics', 'Glass',
        'Civil Engineering', 'Commercial Construction', 'Residential Construction', 'Construction Services',
        'Water Supplies', 'Waste Management', 'Machinery & Equipment', 'Office Machinery & Equipment',
        'Specialist Medical Equipment & Supplies', 'Fishing', 'Manufacturer of Apparel', 'Cargo Services',
        'Warehousing', 'Transportation Infrastructure', 'Iron Ore', 'Quarrying', 'Mining Support Services',
        'Metal Products', 'Downstream', 'Refining', 'Oil Petroleum Sales & Marketing', 'Midstream',
        'Engineering Scientific Services', 'Forensics', 'Data Processing', 'Industrial Cleaning Services',
        'Wholesale Services', 'Food Wholesale', 'Textile & Apparel Wholesale', 'Automotive Wholesale',
        'Electric & Electronic Equipment Wholesale', 'Retail Banking', 'Retail Lending', 'Retail Mortgages',
        'Equity Trading', 'Debt Trading', 'Fixed Income (Capital Markets)', 'Interdealer Broking', 'Derivatives',
        'Commodities', 'Forex', 'Futures & Options', 'Research & Analysis (Capital Markets)', 'Transaction Banking',
        'Cash Management', 'Prime Brokerage', 'Finance Risk Management', 'Leveraged Finance', 'Loan Syndication',
        'Debt Structuring', 'Compliance (Finance Risk Management)', 'Pension Fund Management',
        'Alternative Investments', 'Wealth Management', 'Actuarial Services', 'Credit & Short Term Lending',
        'Savings (Consumer Finance)', 'Leasing (Consumer Finance)', 'Fund Administration', 'Credit Assessment',
        'Treasury', 'Insurance Underwriters', 'Property Insurance', 'Private Equity', 'Venture Capital',
        'Application Development', 'System & Network Management', 'Data Analytics', 'Storage', 'Mobility', 'Digital',
        'Cyber Security', 'Cable', 'Satellite', 'Voice Over IP (VOIP)', 'Service Provider',
        'Application Service Provider', 'Internet Service Provider', 'Wireless', 'Web Hosting', 'Datacentre',
        'Technology Manufacturing', 'Hardware Manufacturing', 'Software Manufacturing', 'IT Consulting',
        'Value Added Reseller', 'Network Integrator', 'Software Integrator', 'Healthcare Technology', 'Research',
        'Testing', 'Diagnostics', 'Pathology', 'Manufacturing Specialist Services', 'Medical Imaging',
        'Healthcare Innovation', 'Additive Manufacturing 3d Printing', 'Medical Devices', 'Forensic Science',
        'Research & Drug Development', 'Drug Manufacturing', 'Drug Regulation', 'Medicine', 'Critical Care Medicine',
        'Family Medicine Gp', 'Psychiatry', 'Surgery', 'Sports Science', 'E-Health', 'Digital Health',
        'Health Analytics & Big Data', 'Animal Health', 'Veterinary Science', 'Private Care & Nursing Homes',
        'Health Insurance', 'OTC Medicines', 'Dentistry', 'Opthamology', 'Central Government',
        'Government Executive Office', 'Parliament', 'Federal', 'Legistlative', 'House of Representatives', 'Senate',
        'Judicial', 'Federal - Executive', 'Military', 'Army', 'Navy', 'Air Force',
        'Membership Organisations (Not For Profit)', 'Grant Maker', 'Academies', 'Further Education',
        'Higher Education', 'Independent Education', 'Primary Secondary', 'Teaching & Learning', 'Private Education',
        'Emergency Response', 'Ambulance', 'Fire', 'Police', 'Life Boat', 'Mountain Rescue', 'Off Shore Rescue',
        'Private Emergency Response', 'Health & Welfare', 'Allied Health Professionals', 'Clinical Health',
        'NHS Trusts', 'Mental Health', 'Clinical Commissioning Groups', 'Other NHS Providers', 'Private Healthcare',
        'Local Government', 'Culture & Sport (Local Government)', 'Environment (Local Government)',
        'Highways & Engineering', 'Neighbourhood & Community Regeneration', 'Housing (Local Government)', 'Offshoring',
        'Nearshoring', 'Homeshoring', 'Contact Centre', 'Audit & Consultancy', 'Tax Services', 'Financial Advisory',
        'Compliance & Risk', 'Strategy Consulting', 'Change Management', 'Advisory', 'Human Capital', 'HR Consulting',
        'Sustainability & Climate Change', 'Facilities Management', 'Catering', 'Security', 'Health & Safety',
        'Maintenance Testing & Inspections', 'Public Services & Not For Profit', 'Regeneration (Local Government)',
        'Insurance', 'Hedge Funds', 'Animal Health Research & Drug Development', 'Specialist Services',
        'Consumer Hardware', 'Cleantech', 'Technology Research & Development', 'Fintech', 'Assurance',
        'Technology Consulting', 'Think Tanks', 'Public Sector Regulation', 'Marine', 'Shipbuilding',
        'Alternative Power', 'Power Retailing', 'Nuclear Energy', 'Plant Equipment Manufacturer',
        'Leisure Goods Retailing', 'Transportation', 'Passenger Air Transport', 'Passenger Rail Transport',
        'Passenger Road Transport', 'Digital Publishing', 'Logistics', 'Food and Beverage Ingredients',
        'Distribution & Logistics', 'Food & Beverages', 'Industrial Infrastructure', 'Environment', 'Recycling',
        'Housing Associations', 'National Security', 'Accountancy Services', 'Academic', 'Contracting',
        'Engineering Services', 'Marketing Services', 'Public Relations', 'Research Services', 'Tax & Revenue',
        'Design Engineering', 'Infrastructure & Capital Projects', 'EPCM', 'Dispute Resolution', 'Expert Witness',
        'Non-Food Retailer', 'Channel Transportation', 'Carbon Trading', 'Executive Search', 'Social Media', 'Theatre',
        'Communications Consultancy (PR)', 'Gaming', 'Clinical Research', 'Clinical Development', 'Pharmacovigilance',
        'Clinical Operations', 'Family Office', 'Automobile Rental', 'Event Management', 'Furniture & Home Furnishings',
        'Telecommunications Operator', 'Industrial Process & Controls', 'News Agencies', 'Environment Consulting',
        'Internet Banking', 'Private Banking', 'Corporate Banking', 'Sports', 'Professional Sports', 'Amateur Sports',
        'Sporting Events', 'Oil & Gas', 'Business Transformation', 'Leadership Consulting', 'Environmental Technology',
        'Renewable Infrastructure', 'Communication Infrastructure', 'Civil Infrastructure',
        'Renewable Infrastructure (Investment)', 'Civil Infrastructure (Investment)',
        'Communication Infrastructure (Investment)', 'Infrastructure Investment',
    ];

    protected static array $techTerms = [
        'AddOn', 'Algorithm', 'Architect', 'Array', 'Asynchronous', 'Avatar',
        'Band', 'Base', 'Beta', 'Binary', 'Blog', 'Board', 'Boolean', 'Boot',
        'Bot', 'Browser', 'Bug', 'Cache', 'Character', 'Checksum', 'Chip',
        'Circuit', 'Client', 'Cloud', 'Cluster', 'Code', 'Codec', 'Coder',
        'Column', 'Command', 'Compile', 'Compression', 'Computing', 'Console',
        'Constant', 'Control', 'Cookie', 'Core', 'Cyber', 'Default', 'Deprecated',
        'Dev', 'Developer', 'Development', 'Device', 'Digital', 'Domain',
        'Dynamic', 'Emulation', 'Encryption', 'Engine', 'Error', 'Exception',
        'Exploit', 'Export', 'Extension', 'File', 'Font', 'Fragment', 'Frame',
        'Function', 'Group', 'Hacker', 'Hard', 'HTTP', 'Icon', 'Input', 'IT',
        'Kernel', 'Key', 'Leak', 'Link', 'Load', 'Logic', 'Mail', 'Mashup',
        'Mega', 'Meme', 'Memory', 'Meta', 'Mount', 'Navigation', 'Net', 'Node',
        'Open', 'OS', 'Output', 'Over', 'Packet', 'Page', 'Parallel', 'Parse',
        'Path', 'Phone', 'Ping', 'Pixel', 'Port', 'Power', 'Programmers',
        'Programs', 'Protocol', 'Push', 'Query', 'Queue', 'Raw', 'Real',
        'Repository', 'Restore', 'Root', 'Router', 'Run', 'Safe', 'Sample',
        'Scalable', 'Script', 'Server', 'Session', 'Shell', 'Smart', 'Socket',
        'Soft', 'Solid', 'Sound', 'Source', 'Streaming', 'Symfony', 'Syntax',
        'System', 'Tag', 'Tape', 'Task', 'Template', 'Thread', 'Token', 'Tool',
        'Tweak', 'URL', 'Utility', 'Viral', 'Volume', 'Ware', 'Web', 'Wiki',
        'Window', 'Wire',
    ];

    protected static array $culinaryTerms = [
        'Appetit', 'Bake', 'Beurre', 'Bistro', 'Blend', 'Boil', 'Bouchees',
        'Brew', 'Buffet', 'Caffe', 'Caffeine', 'Cake', 'Carve', 'Caviar',
        'Chef', 'Chocolate', 'Chop', 'Citrus', 'Cocoa', 'Compote', 'Cook',
        'Cooker', 'Cookery', 'Cool', 'Core', 'Coulis', 'Course', 'Crouton',
        'Cuisine', 'Dash', 'Dessert', 'Dip', 'Dish', 'Dress', 'Entree',
        'Espresso', 'Extracts', 'Fajitas', 'Fibers', 'Fold', 'Formula', 'Fruit',
        'Fumet', 'Fusion', 'Gastronomy', 'Glucose', 'Gourmet', 'Grains',
        'Gratin', 'Greens', 'Guacamole', 'Herbs', 'Honey', 'Hybrid', 'Ice',
        'Icing', 'Immersion', 'Induction', 'Instant', 'Jasmine', 'Jelly',
        'Juice', 'Kiwi', 'Lean', 'Leek', 'Legumes', 'Lemon', 'Lime', 'Liqueur',
        'Madeleine', 'Mango', 'Marinate', 'Melon', 'Mill', 'Mince', 'Mirepoix',
        'Mix', 'Mousse', 'Muffin', 'Mull', 'Munster', 'Nectar', 'Nut', 'Olive',
        'Organic', 'Organic', 'Pan', 'Papillote', 'Pare', 'Pasta', 'Pate',
        'Peanut', 'Pear', 'Pesto', 'Picante', 'Pie', 'Pigment', 'Pinot',
        'Plate', 'Plum', 'Pod', 'Prepare', 'Pressure', 'Pudding', 'Pulp',
        'Quiche', 'Rack', 'Raft', 'Raisin', 'Recipe', 'Reduce', 'Relish',
        'Render', 'Risotto', 'Rosemary', 'Roux', 'Rub', 'Salad', 'Salsa',
        'Sauce', 'Sauté', 'Season', 'Slice', 'Smoked', 'Soft', 'Sorbet', 'Soup',
        'Spaghetti', 'Specialty', 'Spicy', 'Splash', 'Steam', 'Stem', 'Sticky',
        'Stuff', 'Sugar', 'Supreme', 'Sushi', 'Sweet', 'Table', 'Tart', 'Taste',
        'Tasting', 'Tea', 'Tender', 'Terrine', 'Tomato', 'Vanilla', 'Wash',
        'Wax', 'Wine', 'Wok', 'Zest',
    ];

    protected static array $companyNameFormats = [
        '{{techTerm}}{{culinaryTerm}}',
        '{{techTerm}}{{techTerm}}',
        '{{culinaryTerm}}{{techTerm}}',
    ];

    /* cspell:enable */

    public static function division()
    {
        return static::randomElement(static::$divisions);
    }

    public static function industry()
    {
        return static::randomElement(static::$industries);
    }

    public static function size(): int
    {
        return static::numberBetween(50, 50_000);
    }

    public static function techTerm()
    {
        return static::randomElement(static::$techTerms);
    }

    public static function culinaryTerm()
    {
        return static::randomElement(static::$culinaryTerms);
    }

    public function companyName(): string
    {
        $format = static::randomElement(static::$companyNameFormats);

        return $this->generator->parse($format);
    }
}
