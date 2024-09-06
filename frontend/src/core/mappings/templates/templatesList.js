export const templatesList = {
    jobSearch: {
        id: 'jobSearch',
        ignoreIfAlsoSelected: 'executiveJobSearch',
        searchTerms: [
            'career',
            'employment',
        ],
        sections: ['CAREER'],
        symbol: 'fa-briefcase',
    },
    executiveJobSearch: {
        id: 'executiveJobSearch',
        searchTerms: [
            'career',
            'jobSearch',
            'employment',
        ],
        sections: ['CAREER'],
        symbol: 'fa-user-tie',
    },
    educationSearch: {
        id: 'educationSearch',
        searchTerms: [
            'career',
        ],
        sections: ['CAREER'],
        symbol: 'fa-school',
    },
    furtherEducation: {
        id: 'furtherEducation',
        searchTerms: [
            'career',
            'training',
            'seminars',
            'furtherEducation',
            'continuingEducation',
        ],
        sections: ['CAREER'],
        symbol: 'fa-diploma',
    },
    networking: {
        id: 'networking',
        searchTerms: [
            'career',
        ],
        sections: ['CAREER'],
        symbol: 'fa-handshake-alt',
    },
    courseManagement: {
        id: 'courseManagement',
        searchTerms: [
            'education',
            'school',
        ],
        sections: ['CAREER'],
        symbol: 'fa-user-graduate',
    },
    writing: {
        id: 'writing',
        searchTerms: [
            'hobbies',
            'books',
        ],
        sections: ['CREATIVE', 'HOBBIES'],
        symbol: 'fa-pen-fancy',
    },
    writingNonFiction: {
        id: 'writingNonFiction',
        searchTerms: [
            'hobbies',
            'books',
        ],
        sections: ['CREATIVE, HOBBIES'],
        symbol: 'fa-pen-alt',
    },
    teachingTracker: {
        id: 'teachingTracker',
        searchTerms: [
            'course',
            'program',
            'class',
        ],
        sections: ['CAREER'],
        symbol: 'fa-chalkboard-teacher',
    },
    tutoring: {
        id: 'tutoring',
        searchTerms: [
            'one-on-one',
        ],
        sections: ['CAREER'],
        symbol: 'fa-chalkboard',
    },
    trip: {
        id: 'trip',
        searchTerms: [
            'leisure',
            'travel',
        ],
        sections: ['EVENTS'],
        symbol: 'fa-luggage-cart',
    },
    travel: {
        displayAfter: 'trip',
        id: 'travel',
        searchTerms: [
            'leisure',
            'trip',
        ],
        sections: ['EVENTS'],
        symbol: 'fa-globe-stand',
    },
    coaching: {
        id: 'coaching',
        searchTerms: [
            'sports',
        ],
        sections: ['CAREER'],
        symbol: 'fa-tennis-ball',
    },
    partyPlanning: {
        id: 'partyPlanning',
        searchTerms: [
            'event',
        ],
        sections: ['EVENTS'],
        symbol: 'fa-party-bell',
    },
    partiesOrganizing: {
        id: 'partiesOrganizing',
        displayAfter: 'partyPlanning',
        searchTerms: [
            'event',
        ],
        sections: ['EVENTS'],
        symbol: 'fa-party-horn',
    },
    weddingPlanning: {
        id: 'weddingPlanning',
        searchTerms: [
            'event',
        ],
        sections: ['EVENTS'],
        symbol: 'fa-rings-wedding',
    },
    weddingsOrganizing: {
        id: 'weddingsOrganizing',
        searchTerms: [
            'event',
        ],
        sections: ['EVENTS'],
        symbol: 'fak fa-wedding',
    },
    activity: {
        id: 'activity',
        searchTerms: [
            'event',
            'leisure',
        ],
        sections: ['EVENTS'],
        symbol: 'fa-location-circle',
    },
    activities: {
        displayAfter: 'activity',
        id: 'activities',
        searchTerms: [
            'event',
            'leisure',
        ],
        sections: ['EVENTS'],
        symbol: 'fa-hiking',
    },
    applicantTracker: {
        id: 'applicantTracker',
        searchTerms: [
            'hiring',
            'recruitment',
        ],
        sections: ['RECRUITMENT', 'BUSINESS'],
        symbol: 'fa-user-check',
    },
    businessRoadmap: {
        id: 'businessRoadmap',
        searchTerms: [
            'planning',
        ],
        sections: ['BUSINESS'],
        symbol: 'fa-user-chart',
    },
    contractorFinder: {
        id: 'contractorFinder',
        searchTerms: [
        ],
        sections: ['RECRUITMENT', 'BUSINESS', 'HOME'],
        symbol: 'fa-id-card-alt',
    },
    contractorTimeTracker: {
        id: 'contractorTimeTracker',
        searchTerms: [
        ],
        sections: ['RECRUITMENT', 'BUSINESS', 'HOME'],
        symbol: 'fa-clock',
    },
    employeeList: {
        id: 'employeeList',
        searchTerms: [
            'staff',
        ],
        sections: ['BUSINESS'],
        symbol: 'fa-user-shield',
    },
    bills: {
        id: 'bills',
        searchTerms: [
            'payments',
            'utilities',
        ],
        sections: ['EVERYDAY_LIFE', 'BUSINESS'],
        symbol: 'fa-money-bill-wave',
    },
    staffSchedule: {
        id: 'staffSchedule',
        searchTerms: [
            'employee',
        ],
        sections: ['BUSINESS'],
        symbol: 'fa-calendar-week',
    },
    suppliers: {
        id: 'suppliers',
        searchTerms: [
        ],
        sections: ['BUSINESS'],
        symbol: 'fa-conveyor-belt',
    },
    projectManagement: {
        displayAfter: 'singleProjectManagement',
        id: 'projectManagement',
        searchTerms: [
        ],
        sections: ['PROJECT_MANAGEMENT', 'FREELANCERS'],
        symbol: 'fa-project-diagram',
    },
    design: {
        id: 'design',
        searchTerms: [
            'hobbies',
            'creative',
        ],
        sections: ['HOBBIES', 'CREATIVE'],
        symbol: 'fa-crop-alt',
    },
    ideas: {
        id: 'ideas',
        searchTerms: [
        ],
        sections: ['EVERYDAY_LIFE', 'CREATIVE'],
        symbol: 'fa-lightbulb-on',
    },
    photographyBusiness: {
        id: 'photographyBusiness',
        searchTerms: [
        ],
        sections: ['CREATIVE'],
        symbol: 'fa-camera',
    },
    bucketList: {
        id: 'bucketList',
        searchTerms: [
        ],
        sections: ['EVERYDAY_LIFE'],
        symbol: 'fa-umbrella-beach',
    },
    documentRenewals: {
        id: 'documentRenewals',
        searchTerms: [
            'passport',
        ],
        sections: ['EVERYDAY_LIFE'],
        symbol: 'fa-passport',
    },
    gardening: {
        id: 'gardening',
        searchTerms: [
            'home',
        ],
        sections: ['EVERYDAY_LIFE', 'HOME'],
        symbol: 'fa-seedling',
    },
    giftIdeas: {
        id: 'giftIdeas',
        searchTerms: [
            'present',
        ],
        sections: ['EVERYDAY_LIFE'],
        symbol: 'fa-gift',
    },
    holidayGifts: {
        id: 'holidayGifts',
        searchTerms: [
            'present',
        ],
        sections: ['EVERYDAY_LIFE'],
        symbol: 'fa-tree-decorated',
    },
    homeImprovement: {
        id: 'homeImprovement',
        searchTerms: [
            'household',
            'home',
        ],
        sections: ['EVERYDAY_LIFE', 'HOME'],
        symbol: 'fa-house-day',
    },
    personalCrm: {
        id: 'personalCrm',
        searchTerms: [
            'friends',
            'family',
        ],
        sections: ['EVERYDAY_LIFE'],
        symbol: 'fa-people-group',
    },
    petCare: {
        id: 'petCare',
        searchTerms: [
            'dog',
            'cat',
            'veterinary',
        ],
        sections: ['EVERYDAY_LIFE'],
        symbol: 'fa-paw',
    },
    recipes: {
        id: 'recipes',
        searchTerms: [
            'food',
            'cooking',
            'baking',
            'home',
            'meal',
        ],
        sections: ['EVERYDAY_LIFE'],
        symbol: 'fa-utensils-alt',
    },
    mealPlanning: {
        id: 'mealPlanning',
        ignoreIfAlsoSelected: 'mealPlanning',
        searchTerms: [
            'food',
            'cooking',
            'baking',
            'home',
            'meal',
        ],
        sections: ['EVERYDAY_LIFE'],
        symbol: 'fa-pot-food',
    },
    vehiclePlanner: {
        id: 'vehiclePlanner',
        searchTerms: [
            'car',
        ],
        sections: ['EVERYDAY_LIFE'],
        symbol: 'fa-car',
    },
    vehicleSearch: {
        id: 'vehicleSearch',
        searchTerms: [
            'car',
        ],
        sections: ['EVERYDAY_LIFE'],
        symbol: 'fa-cars',
    },
    homeBuying: {
        id: 'homeBuying',
        searchTerms: [
            'home',
            'apartment',
        ],
        sections: ['HOME', 'REAL_ESTATE'],
        symbol: 'fa-home-heart',
    },
    rentalSearch: {
        id: 'rentalSearch',
        searchTerms: [
            'home',
            'apartment',
        ],
        sections: ['HOME', 'REAL_ESTATE'],
        symbol: 'fa-house-person-return',
    },
    hobbiesArt: {
        id: 'hobbiesArt',
        searchTerms: [
            'hobbies',
            'drawing',
            'painting',
            'sculpting',
        ],
        sections: ['HOBBIES'],
        symbol: 'fa-pencil-paintbrush',
    },
    hobbiesCollecting: {
        id: 'hobbiesCollecting',
        searchTerms: [
            'hobbies',
        ],
        sections: ['HOBBIES'],
        symbol: 'fa-album-collecting',
    },
    hobbiesGaming: {
        id: 'hobbiesGaming',
        searchTerms: [
            'hobbies',
        ],
        sections: ['HOBBIES'],
        symbol: 'fa-gamepad',
    },
    jewelryMaking: {
        id: 'jewelryMaking',
        searchTerms: [
            'hobbies',
        ],
        sections: ['HOBBIES'],
        symbol: 'fa-ring',
    },
    knitting: {
        id: 'knitting',
        searchTerms: [
            'hobbies',
        ],
        sections: ['HOBBIES'],
        symbol: 'fa-scarf',
    },
    photographyHobby: {
        id: 'photographyHobby',
        searchTerms: [
            'hobbies',
        ],
        sections: ['HOBBIES'],
        symbol: 'fa-camera-alt',
    },
    hobbiesProjects: {
        id: 'hobbiesProjects',
        searchTerms: [
            'projects',
            'crafts',
            'making',
            'building',
            'hobbies',
        ],
        sections: ['HOBBIES'],
        symbol: 'fa-ruler-combined',
    },
    reading: {
        id: 'reading',
        searchTerms: [
            'hobbies',
            'books',
        ],
        sections: ['HOBBIES'],
        symbol: 'fa-book-open',
    },
    sewing: {
        id: 'sewing',
        searchTerms: [
            'hobbies',
            'clothes',
        ],
        sections: ['HOBBIES'],
        symbol: 'fa-cut',
    },
    woodworking: {
        id: 'woodworking',
        searchTerms: [
            'projects',
            'crafts',
            'hobbies',
        ],
        sections: ['HOBBIES'],
        symbol: 'fa-toolbox',
    },
    worldBuilding: {
        id: 'worldBuilding',
        searchTerms: [
            'writing',
            'hobbies',
        ],
        sections: ['HOBBIES'],
        symbol: 'fa-planet-moon',
    },
    singleProjectManagement: {
        id: 'singleProjectManagement',
        searchTerms: [
        ],
        sections: ['PROJECT_MANAGEMENT'],
        symbol: 'fa-clipboard',

    },
    crm: {
        id: 'crm',
        searchTerms: [
            'contacts',
        ],
        sections: ['SALES'],
        symbol: 'fa-poll-people',

    },
    clients: {
        id: 'clients',
        searchTerms: [
            'contacts',
            'clients',
            'customers',
            'people',
        ],
        sections: ['SALES', 'FREELANCERS'],
        symbol: 'fa-address-card',

    },
    jobPlacements: {
        id: 'jobPlacements',
        searchTerms: [
            'recruitment',
        ],
        sections: ['RECRUITMENT'],
        symbol: 'fa-users-medical',

    },
    propertyManagement: {
        id: 'propertyManagement',
        searchTerms: [
        ],
        sections: ['REAL_ESTATE'],
        symbol: 'fa-screwdriver',

    },
    salesAndRentals: {
        id: 'salesAndRentals',
        searchTerms: [
            'property',
        ],
        sections: ['REAL_ESTATE'],
        symbol: 'fa-laptop-house',

    },
    viewings: {
        id: 'viewings',
        searchTerms: [
            'property',
        ],
        sections: ['REAL_ESTATE'],
        symbol: 'fa-house-user',

    },
    bugTracker: {
        id: 'bugTracker',
        searchTerms: [
            'software',
        ],
        sections: ['SOFTWARE'],
        symbol: 'fa-debug',

    },
    blogPlanning: {
        id: 'blogPlanning',
        searchTerms: [
            'blogging',
        ],
        sections: ['CONTENT_PRODUCTION'],
        symbol: 'fa-blog',

    },
    contentCreation: {
        id: 'contentCreation',
        searchTerms: [
            'socialMedia',
            'blogging',
        ],
        sections: ['CONTENT_PRODUCTION'],
        symbol: 'fa-photo-video',

    },
    socialMediaPlanning: {
        id: 'socialMediaPlanning',
        searchTerms: [
        ],
        sections: ['CONTENT_PRODUCTION'],
        symbol: 'fa-hashtag',

    },
    starterItems: {
        id: 'starterItems',
        searchTerms: [
            'item',
        ],
        sections: ['STARTERS'],
        symbol: 'fa-circle-half-stroke',
    },
    starterPeople: {
        id: 'starterPeople',
        searchTerms: [
            'person',
        ],
        sections: ['STARTERS'],
        symbol: 'fa-square-user',
    },
    productPlanning: {
        id: 'productPlanning',
        searchTerms: [
        ],
        sections: ['PRODUCT_DEVELOPMENT'],
        symbol: 'fa-chart-tree-map',
    },
    productRoadmap: {
        id: 'productRoadmap',
        searchTerms: [
        ],
        sections: ['PRODUCT_DEVELOPMENT'],
        symbol: 'fa-road',
    },
    competitorTracking: {
        id: 'competitorTracking',
        searchTerms: [
            'product',
        ],
        sections: ['PRODUCT_DEVELOPMENT'],
        symbol: 'fa-monitor-waveform',
    },
};

export default { templatesList };

// FOR LATER
// Volunteer management ----
// Donations ------
// Fundraiser ------
// Grant writing ------
// Fashion
// Editing
// Publishing
// Tournament organization - Events

// conferencePlanning:
//     id: 'conferencePlanning',
//     searchTerms: [
//         'event',
//     ],
//     sections: ['EVENTS'],
//     symbol: 'fa-presentation',

// },
// conferencesOrganizing:
//     id: 'conferencesOrganizing',
//     searchTerms: [
//         'event',
//     ],
//     sections: ['EVENTS'],
//     symbol: 'fa-podium',
// },
