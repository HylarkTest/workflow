export const uses = {
    // Work
    BUSINESS: {
        val: 'BUSINESS',
        collab: true,
        searchTerms: [
            'freelance',
            'business',
            'project',
            'product',
            'client',
            'company',
            'supplier',
            'vendor',
            'staff',
            'hiring',
            'employees',
            'property',
            'human',
            'hr',
            'event',
        ],
        tags: ['tailorToYou'],
        categories: ['workAndBusiness'],
        initial: true,
        bundle: 'business',
        refinementMap: {
            customizations: [
                {
                    val: 'CUSTOMERS',
                    canTurnOff: true,
                    categories: {
                        clients: {
                            ignoreHeader: true,
                            prompt: 'whoClients',
                            post: 'addClientsLater',
                            radio: true,
                            fetchBundles: ['clients'],
                            actsOn: 'bundlePages',
                            options: [
                                {
                                    optionVal: 'clientsPeople',
                                    pageVal: 'CLIENT_PERSON',
                                },
                                {
                                    optionVal: 'clientsCompanies',
                                    pageVal: 'CLIENT_ORGANIZATION',
                                },
                                {
                                    optionVal: 'clientsBothTogether',
                                    pageVal: 'CLIENT_GENERIC',
                                },
                                {
                                    optionVal: 'clientsBothSeparate',
                                    pageVal: ['CLIENT_PERSON', 'CLIENT_ORGANIZATION'],
                                },
                                {
                                    optionVal: 'other',
                                    pageVal: 'CLIENT_GENERIC',
                                },
                            ],
                        },
                    },
                },
                {
                    val: 'PROJECTS',
                    canTurnOff: true,
                    categories: {
                        projects: {
                            ignoreHeader: true,
                            radio: true,
                            fetchBundles: ['projectManagement'],
                            actsOn: 'bundlePages',
                            options: [
                                {
                                    optionVal: 'oneProject',
                                    pageVal: 'SINGLE_PROJECT',
                                },
                                {
                                    optionVal: 'multipleProjects',
                                    pageVal: 'PROFESSIONAL_PROJECT',
                                },
                            ],
                        },
                    },
                },
                {
                    val: 'PRODUCTS',
                    canTurnOff: true,
                    categories: {
                        products: {
                            ignoreHeader: true,
                            radio: true,
                            fetchBundles: 'products',
                            actsOn: 'bundlePages',
                            options: [
                                {
                                    optionVal: 'oneProduct',
                                    pageVal: 'SINGLE_PRODUCT',
                                },
                                {
                                    optionVal: 'multipleProducts',
                                    pageVal: 'PROFESSIONAL_PRODUCT',
                                },
                            ],
                        },
                    },
                },
            ],
        },
        refinement: {
            done: false,
            customizations: [
                {
                    active: true,
                    customizationVal: 'CUSTOMERS',
                    categoryKey: 'clients',
                    selected: '',
                },
                {
                    active: true,
                    customizationVal: 'PROJECTS',
                    categoryKey: 'projects',
                    selected: 'multipleProjects',
                },
                {
                    active: true,
                    customizationVal: 'PRODUCTS',
                    categoryKey: 'products',
                    selected: 'multipleProducts',
                },
            ],
        },
        // Clients
        // Projects
        // Product
        // Roadmap
        // Investors
        // Hiring
        // Services and suppliers
    },
    TEACHING: {
        val: 'TEACHING',
        collab: true,
        searchTerms: [
            'coach',
            'teach',
            'tutor',
            'club',
            'group',
            'scouts',
            'student',
            'class',
            'organizer',
            'course',
        ],
        categories: ['workAndBusiness'],
        refinementMap: {
            customizations: [
                {
                    val: 'STUDENT_WORK_TYPE',
                    categories: {
                        studentWorkType: {
                            radio: true,
                            ignoreHeader: true,
                            actsOn: 'bundle',
                            options: [
                                {
                                    optionVal: 'coach',
                                    bundle: 'coaching',
                                },
                                {
                                    optionVal: 'tutor',
                                    bundle: 'tutoring',
                                },
                                {
                                    optionVal: 'teacher',
                                    bundle: 'teaching',
                                },
                                {
                                    optionVal: 'other',
                                    bundle: 'teaching',
                                },
                            ],
                        },
                    },
                },
                {
                    val: 'STUDENTS',
                    categories: {
                        students: {
                            ignoreHeader: true,
                            radio: true,
                            actsOn: 'bundlePages',
                            options: [
                                {
                                    optionVal: 'yes',
                                    useKey: 'student',
                                },
                                {
                                    optionVal: 'no',
                                },
                            ],
                        },
                    },
                },
                {
                    val: 'COURSES',
                    categories: {
                        courses: {
                            ignoreHeader: true,
                            radio: true,
                            actsOn: 'bundlePages',
                            options: [
                                {
                                    optionVal: 'yes',
                                    useKey: 'course',
                                },
                                {
                                    optionVal: 'no',
                                },
                            ],
                        },
                    },
                },
                {
                    val: 'ASSIGNMENTS',
                    categories: {
                        assignments: {
                            ignoreHeader: true,
                            radio: true,
                            actsOn: 'bundlePages',
                            options: [
                                {
                                    optionVal: 'yes',
                                    useKey: 'assignment',
                                },
                                {
                                    optionVal: 'no',
                                },
                            ],
                        },
                    },
                },
            ],
        },
        refinement: {
            done: false,
            customizations: [
                {
                    customizationVal: 'STUDENT_WORK_TYPE',
                    categoryKey: 'studentWorkType',
                    selected: 'other',
                },
                {
                    customizationVal: 'STUDENTS',
                    categoryKey: 'students',
                    selected: 'yes',
                },
                {
                    customizationVal: 'COURSES',
                    categoryKey: 'courses',
                    selected: 'yes',
                },
                {
                    customizationVal: 'ASSIGNMENTS',
                    categoryKey: 'assignments',
                    selected: 'yes',
                },
            ],
        },
    },
    WORK: {
        val: 'WORK',
        collab: true,
        searchTerms: [
            'project',
            'product',
            'client',
            'company',
            'supplier',
            'vendor',
            'staff',
            'hiring',
            'employees',
            'property',
            'human',
            'hr',
            'event',
        ],
        categories: ['workAndBusiness'],
        initial: true,
        tags: ['tailorToYou'],
        bundle: 'work',
        refinementMap: {
            customizations: [
                {
                    val: 'CUSTOMERS',
                    canTurnOff: true,
                    categories: {
                        clients: {
                            ignoreHeader: true,
                            prompt: 'whoClients',
                            post: 'addClientsLater',
                            radio: true,
                            fetchBundles: ['clients'],
                            actsOn: 'bundlePages',
                            options: [
                                {
                                    optionVal: 'clientsPeople',
                                    pageVal: 'CLIENT_PERSON',
                                },
                                {
                                    optionVal: 'clientsCompanies',
                                    pageVal: 'CLIENT_ORGANIZATION',
                                },
                                {
                                    optionVal: 'clientsBothTogether',
                                    pageVal: 'CLIENT_GENERIC',
                                },
                                {
                                    optionVal: 'clientsBothSeparate',
                                    pageVal: ['CLIENT_PERSON', 'CLIENT_ORGANIZATION'],
                                },
                                {
                                    optionVal: 'other',
                                    pageVal: 'CLIENT_GENERIC',
                                },
                            ],
                        },
                    },
                },
                {
                    val: 'PROJECTS',
                    canTurnOff: true,
                    categories: {
                        projects: {
                            ignoreHeader: true,
                            radio: true,
                            fetchBundles: ['projectManagement'],
                            actsOn: 'bundlePages',
                            options: [
                                {
                                    optionVal: 'oneProject',
                                    pageVal: 'SINGLE_PROJECT',
                                },
                                {
                                    optionVal: 'multipleProjects',
                                    pageVal: 'PROFESSIONAL_PROJECT',
                                },
                            ],
                        },
                    },
                },
                {
                    val: 'PRODUCTS',
                    canTurnOff: true,
                    categories: {
                        products: {
                            ignoreHeader: true,
                            radio: true,
                            fetchBundles: ['products'],
                            actsOn: 'bundlePages',
                            options: [
                                {
                                    optionVal: 'oneProduct',
                                    pageVal: 'SINGLE_PRODUCT',
                                },
                                {
                                    optionVal: 'multipleProducts',
                                    pageVal: 'PROFESSIONAL_PRODUCT',
                                },
                            ],
                        },
                    },
                },
            ],
        },
        refinement: {
            done: false,
            customizations: [
                {
                    active: true,
                    customizationVal: 'CUSTOMERS',
                    categoryKey: 'clients',
                    selected: '',
                },
                {
                    active: true,
                    customizationVal: 'PROJECTS',
                    categoryKey: 'projects',
                    selected: 'multipleProjects',
                },
                {
                    active: true,
                    customizationVal: 'PRODUCTS',
                    categoryKey: 'products',
                    selected: 'multipleProducts',
                },
            ],
        },
        // Clients
        // Projects
        // Product
        // Roadmap
        // Investors
        // Hiring
        // Services and vendors
    },

    // Career
    // Personal profile?
    FIND_JOB: {
        val: 'FIND_JOB',
        searchTerms: [
            'interview',
            'position',
            'organization',
            'company',
            'career',
            'professional',
            'work',
            'job',
            'network',
        ],
        ignoreIfAlsoSelected: ['EXECUTIVE_CAREER'],
        categories: ['career'],
        initial: true,
        bundle: 'jobSearch',
    },
    NETWORK: {
        val: 'NETWORK',
        ignoreIfAlsoSelected: ['EXECUTIVE_CAREER', 'FIND_JOB'],
        searchTerms: [
            'network',
            'organization',
            'professional',
            'contact',
        ],
        categories: ['career'],
        initial: true,
        bundle: 'networking',
        // People professional
    },
    EXECUTIVE_CAREER: {
        val: 'EXECUTIVE_CAREER',
        searchTerms: [
            'executive',
            'career',
            'professional',
            'work',
            'job',
            'network',
            'interview',
            'position',
            'organization',
            'company',
        ],
        categories: ['career'],
        initial: true,
        bundle: 'executiveJobSearch',
    },

    // Education
    EDUCATION_SEARCH: {
        val: 'EDUCATION_SEARCH',
        searchTerms: [
            'university',
            'student',
            'education search',
            'college',
        ],
        categories: ['education'],
        bundle: 'educationSearch',
    },
    CONTINUING_EDUCATION: {
        val: 'CONTINUING_EDUCATION',
        searchTerms: [
            'education',
            'course',
            'professional',
            'career',
        ],
        categories: ['education'],
        initial: true,
        bundle: 'furtherEducation',
    },
    UNIVERSITY_EXPERIENCE: {
        collab: true,
        val: 'UNIVERSITY_EXPERIENCE',
        searchTerms: [
            'courses',
            'grades',
            'extracurricular',
            'study',
            'exam',
        ],
        categories: ['education'],
        bundle: 'courseManagement',
    },

    // Everyday
    CRM_LIFE: {
        val: 'CRM_LIFE',
        collab: true,
        searchTerms: [
            'crm',
            'life',
            'crm for your life',
            'every day',
            'network',
        ],
        categories: ['everyday'],
        initial: true,
        bundle: 'crmLife',
        refinementMap: {
            customizations: [
                {
                    val: 'CRM_LIFE_EXTRAS',
                    optional: true,
                    categories: {
                        crmLifeExtras: {
                            ignoreHeader: true,
                            actsOn: 'bundle',
                            options: [
                                {
                                    optionVal: 'subscriptions',
                                    bundle: 'subscriptions',
                                },
                                {
                                    optionVal: 'vehicles',
                                    bundle: 'vehiclePlanner',
                                },
                                {
                                    optionVal: 'travel',
                                    bundle: 'travel',
                                },
                                {
                                    optionVal: 'home',
                                    bundle: 'homeImprovement',
                                },
                                {
                                    optionVal: 'bills',
                                    bundle: 'bills',
                                },
                                // {
                                //     optionVal: 'finance',
                                //     bundle: 'finance',
                                // },
                            ],
                        },
                    },
                },
                {
                    val: 'CHILDREN_NUMBER',
                    canTurnOff: true,
                    categories: {
                        childrenNumber: {
                            ignoreHeader: true,
                            radio: true,
                            fetchBundles: ['childrensSchedule'],
                            actsOn: 'pageType',
                            options: [
                                {
                                    optionVal: 'ENTITIES',
                                    pathKey: 'multipleChildren',
                                    pageTypeChanges: [
                                        {
                                            page: 'CHILD',
                                            pageNameKey: 'children',
                                        },
                                    ],
                                },
                                {
                                    optionVal: 'ENTITY',
                                    pathKey: 'singleChild',
                                    pageTypeChanges: [
                                        {
                                            page: 'CHILD',
                                            pageNameKey: 'child',
                                        },
                                    ],
                                },
                            ],
                        },
                    },
                },
                {
                    val: 'PETS_NUMBER',
                    canTurnOff: true,
                    categories: {
                        petsNumber: {
                            ignoreHeader: true,
                            radio: true,
                            actsOn: 'pageType',
                            fetchBundles: ['petCare'],
                            options: [
                                {
                                    optionVal: 'ENTITIES',
                                    pathKey: 'multiplePets',
                                    pageTypeChanges: [
                                        {
                                            page: 'PET',
                                            pageNameKey: 'pets',
                                        },
                                    ],
                                },
                                {
                                    optionVal: 'ENTITY',
                                    pathKey: 'singlePet',
                                    pageTypeChanges: [
                                        {
                                            page: 'PET',
                                            pageNameKey: 'pet',
                                        },
                                    ],
                                },
                            ],
                        },
                    },
                },
            ],
        },
        refinement: {
            done: false,
            customizations: [
                {
                    customizationVal: 'CRM_LIFE_EXTRAS',
                    categoryKey: 'crmLifeExtras',
                    selected: ['subscriptions', 'vehicles', 'travel', 'home', 'bills'],
                },
                {
                    active: true,
                    customizationVal: 'CHILDREN_NUMBER',
                    categoryKey: 'childrenNumber',
                    selected: 'ENTITIES',
                },
                {
                    active: true,
                    customizationVal: 'PETS_NUMBER',
                    categoryKey: 'petsNumber',
                    selected: 'ENTITIES',
                },
            ],
        },
    },
    HOME_IMPROVEMENTS: {
        val: 'HOME_IMPROVEMENTS',
        collab: true,
        searchTerms: [
            'home improvement',
            'home',
            'house',
        ],
        ignoreIfAlsoSelected: ['CRM_LIFE'],
        categories: ['everyday'],
        initial: true,
        bundle: 'homeImprovement',
    },
    EXPENSES: {
        val: 'EXPENSES',
        collab: true,
        searchTerms: [
            'bills',
            'expenses',
        ],
        ignoreIfAlsoSelected: ['CRM_LIFE'],
        categories: ['everyday'],
        initial: true,
        bundle: 'bills',
    },
    SUBSCRIPTIONS: {
        val: 'SUBSCRIPTIONS',
        collab: true,
        searchTerms: [
            'subscriptions',
        ],
        ignoreIfAlsoSelected: ['CRM_LIFE'],
        categories: ['everyday', 'workAndBusiness'],
        initial: true,
        bundle: 'subscriptions',
    },
    PET_CARE: {
        val: 'PET_CARE',
        collab: true,
        searchTerms: [
            'dog',
            'cat',
            'vet',
            'pet',
            'animal',
        ],
        categories: ['everyday'],
        bundle: 'petCare',
    },
    CHILDREN_SCHEDULE: {
        val: 'CHILDREN_SCHEDULE',
        collab: true,
        searchTerms: [
            'calendar',
            'schedule',
            'children',
            'parent',
            'activities',
            'busy',
        ],
        categories: ['everyday'],
        initial: true,
        bundle: 'childrensSchedule',
    },
    RECIPES: {
        val: 'RECIPES',
        collab: true,
        searchTerms: [
            'recipes',
            'food',
            'meal',
            'cook',
        ],
        categories: ['everyday'],
        initial: true,
        bundle: 'mealPrep',
    },
    GIFTS: {
        val: 'GIFTS',
        collab: true,
        searchTerms: [
            'gifts',
            'presents',
            'birthday',
            'anniversary',
            'christmas',
        ],
        categories: ['everyday', 'special'],
        initial: true,
        bundle: 'gifts',
    },
    GARDENING: {
        val: 'GARDENING',
        collab: true,
        searchTerms: [
            'gardening',
            'home',
        ],
        categories: ['everyday'],
        bundle: 'gardening',
    },
    PERSONAL_CRM: {
        val: 'PERSONAL_CRM',
        collab: true,
        searchTerms: [
            'contact',
            'friends',
            'family',
            'crm',
        ],
        categories: ['everyday'],
        bundle: 'personalCrm',
    },
    VEHICLE_MAINTENANCE: {
        val: 'VEHICLE_MAINTENANCE',
        collab: true,
        searchTerms: [
            'vehicle',
            'car',
            'truck',
            'motorcycle',
            'maintenance',
        ],
        ignoreIfAlsoSelected: ['CRM_LIFE'],
        categories: ['everyday'],
        bundle: 'vehiclePlanner',
    },
    // VEHICLE_SEARCH: {
    //     val: 'VEHICLE_SEARCH',
    //     searchTerms: [
    //     ],
    //     categories: ['everyday'],
    //     pages: [
    //         {
    //             val: '',
    //         },
    //     ],
    // },
    // HOME_SEARCH: {
    //     val: 'HOME_SEARCH',
    //     searchTerms: [
    //     ],
    //     categories: ['everyday'],
    // },

    // Hobbies
    HOBBY_PROJECTS: {
        val: 'HOBBY_PROJECTS',
        collab: true,
        searchTerms: [
            'project',
            'hobby',
            'creative',
            'art',
            'drawing',
            'painting',
            'sewing',
            'woodworking',
            'craft',
            'jewelry',
        ],
        categories: ['hobbies'],
        initial: true,
        refinementMap: {
            customizations: [
                {
                    val: 'PROJECT_TYPE',
                    categories: {
                        projectType: {
                            ignoreHeader: true,
                            actsOn: 'bundle',
                            options: [
                                {
                                    optionVal: 'sewing',
                                    bundle: 'sewing',
                                },
                                {
                                    optionVal: 'woodworking',
                                    bundle: 'woodworking',
                                },
                                {
                                    optionVal: 'photography',
                                    bundle: 'photographyHobby',
                                },
                                {
                                    optionVal: 'knitting',
                                    bundle: 'knitting',
                                },
                                {
                                    optionVal: 'art',
                                    bundle: 'hobbiesArt',
                                },
                                {
                                    optionVal: 'other',
                                    bundle: 'hobbiesProjects',
                                },
                            ],
                        },
                    },
                },
            ],
        },
        refinement: {
            done: false,
            customizations: [
                {
                    customizationVal: 'PROJECT_TYPE',
                    categoryKey: 'projectType',
                    selected: [],
                },
            ],
        },
    },
    READING: {
        val: 'READING',
        collab: true,
        searchTerms: [
            'book',
            'reading',
            'hobby',
        ],
        categories: ['hobbies'],
        bundle: 'reading',
    },
    WRITING: {
        val: 'WRITING',
        collab: true,
        searchTerms: [
            'story',
            'fiction',
            'character',
            'nonfiction',
            'write',
            'book',
        ],
        categories: ['hobbies'],
        initial: true,
        refinementMap: {
            customizations: [
                {
                    val: 'WRITING_TYPE',
                    categories: {
                        writingType: {
                            ignoreHeader: true,
                            actsOn: 'bundle',
                            options: [
                                {
                                    optionVal: 'fiction',
                                    bundle: 'writing',
                                },
                                {
                                    optionVal: 'nonFiction',
                                    bundle: 'writingNonFiction',
                                },
                            ],
                        },
                    },
                },
                {
                    val: 'BOOK_NUMBER',
                    categories: {
                        bookNumber: {
                            radio: true,
                            ignoreHeader: true,
                            actsOn: 'pageType',
                            options: [
                                {
                                    optionVal: 'ENTITIES',
                                    pathKey: 'multipleBooks',
                                    pageTypeChanges: [
                                        {
                                            page: 'STORY',
                                            pageNameKey: 'allMyStories',
                                        },
                                        {
                                            page: 'NON_FICTION_BOOK',
                                            pageNameKey: 'allMyNonFictionBooks',
                                        },
                                    ],
                                },
                                {
                                    optionVal: 'ENTITY',
                                    pathKey: 'oneBook',
                                    pageTypeChanges: [
                                        {
                                            page: 'STORY',
                                            pageNameKey: 'myStory',
                                        },
                                        {
                                            page: 'NON_FICTION_BOOK',
                                            pageNameKey: 'myNonFictionBook',
                                        },
                                    ],
                                },
                            ],
                        },
                    },
                },
            ],
        },
        refinement: {
            done: false,
            customizations: [
                {
                    customizationVal: 'WRITING_TYPE',
                    categoryKey: 'writingType',
                    selected: ['fiction'],
                },
                {
                    customizationVal: 'BOOK_NUMBER',
                    categoryKey: 'bookNumber',
                    selected: 'ENTITIES',
                },
            ],
        },
    },
    COLLECTING: {
        val: 'COLLECTING',
        collab: true,
        searchTerms: [
            'collect',
            'item',
            'hobby',
        ],
        categories: ['hobbies'],
        bundle: 'hobbiesCollecting',
    },
    // WORLDBUILDING: {
    //     val: 'WORLDBUILDING',
    //     searchTerms: [
    //     ],
    //     categories: ['hobbies'],
    // },

    // Special
    WEDDING: {
        val: 'WEDDING',
        collab: true,
        searchTerms: [
            'wedding',
            'guest',
            'venue',
            'vendor',
        ],
        categories: ['special'],
        initial: true,
        bundle: 'weddingPlanning',
        refinementMap: {
            customizations: [
                {
                    val: 'WEDDING_EXTRAS',
                    optional: true,
                    categories: {
                        weddingExtras: {
                            ignoreHeader: true,
                            actsOn: 'bundlePages',
                            options: [
                                {
                                    optionVal: 'guests',
                                    pageVal: ['WEDDING_GUEST_SUBSET', 'PERSON'],
                                },
                                {
                                    optionVal: 'vendorsAndServices',
                                    pageVal: 'WEDDING_SERVICE',
                                },
                                {
                                    optionVal: 'potentialVenues',
                                    pageVal: 'WEDDING_VENUE',
                                },
                            ],
                        },
                    },
                },
            ],
        },
        refinement: {
            done: false,
            customizations: [
                {
                    customizationVal: 'WEDDING_EXTRAS',
                    categoryKey: 'weddingExtras',
                    selected: ['guests', 'potentialVenues', 'vendorsAndServices'],
                },
            ],
        },
    },
    TRAVEL: {
        val: 'TRAVEL',
        collab: true,
        searchTerms: [
            'travel',
            'destinations',
            'accommodation',
            'activities',
        ],
        ignoreIfAlsoSelected: ['CRM_LIFE'],
        categories: ['special'],
        initial: true,
        bundle: 'travel',
        refinementMap: {
            customizations: [
                {
                    val: 'DESTINATION_NUMBER',
                    categories: {
                        destinationNumber: {
                            radio: true,
                            ignoreHeader: true,
                            actsOn: 'pageType',
                            options: [
                                {
                                    optionVal: 'ENTITIES',
                                    pathKey: 'multipleDestinations',
                                    pageTypeChanges: [
                                        {
                                            page: 'DESTINATION',
                                            pageNameKey: 'destinations',
                                        },
                                    ],
                                },
                                {
                                    optionVal: 'ENTITY',
                                    pathKey: 'oneDestination',
                                    pageTypeChanges: [
                                        {
                                            page: 'DESTINATION',
                                            pageNameKey: 'trip',
                                        },
                                    ],
                                },
                            ],
                        },
                    },
                },
                {
                    val: 'TRAVEL_EXTRAS',
                    optional: true,
                    categories: {
                        travelExtras: {
                            ignoreHeader: true,
                            actsOn: 'bundlePages',
                            options: [
                                {
                                    optionVal: 'travelAccommodation',
                                    pageVal: 'ACCOMMODATION',
                                },
                                {
                                    optionVal: 'travelActivities',
                                    pageVal: 'TRAVEL_ACTIVITY',
                                },
                            ],
                        },
                    },
                },
            ],
        },
        refinement: {
            done: false,
            customizations: [
                {
                    customizationVal: 'DESTINATION_NUMBER',
                    categoryKey: 'destinationNumber',
                    selected: 'ENTITIES',
                },
                {
                    customizationVal: 'TRAVEL_EXTRAS',
                    categoryKey: 'travelExtras',
                    selected: ['travelAccommodation', 'travelActivities'],
                },
            ],
        },
    },
    BUCKET_LIST: {
        val: 'BUCKET_LIST',
        collab: true,
        searchTerms: [
            'bucket list',
            'activities',
        ],
        categories: ['special'],
        bundle: 'bucketList',
    },
    ACTIVITIES: {
        val: 'ACTIVITIES',
        collab: true,
        searchTerms: [
            'activity',
            'sports',
            'days out',
            'days in',
            'fun',
        ],
        categories: ['special'],
        initial: true,
        bundle: 'activity',
        refinementMap: {
            customizations: [
                {
                    val: 'ACTIVITY_NUMBER',
                    categories: {
                        activityNumber: {
                            radio: true,
                            ignoreHeader: true,
                            actsOn: 'pageType',
                            options: [
                                {
                                    optionVal: 'ENTITIES',
                                    pathKey: 'multipleActivities',
                                    pageTypeChanges: [
                                        {
                                            page: 'ACTIVITY',
                                            pageNameKey: 'activities',
                                        },
                                    ],
                                },
                                {
                                    optionVal: 'ENTITY',
                                    pathKey: 'oneActivity',
                                    pageTypeChanges: [
                                        {
                                            page: 'ACTIVITY',
                                            pageNameKey: 'myActivity',
                                        },
                                    ],
                                },
                            ],
                        },
                    },
                },
            ],
        },
        refinement: {
            done: false,
            customizations: [
                {
                    customizationVal: 'ACTIVITY_NUMBER',
                    categoryKey: 'activityNumber',
                    selected: 'ENTITIES',
                },
            ],
        },
    },
    EVENTS: {
        val: 'EVENTS',
        collab: true,
        searchTerms: [
            'event',
            'guest',
            'party',
            'parties',
        ],
        categories: ['special'],
        initial: true,
        bundle: 'events',
        refinementMap: {
            customizations: [
                {
                    val: 'EVENT_NUMBER',
                    categories: {
                        eventNumber: {
                            radio: true,
                            ignoreHeader: true,
                            actsOn: 'pageType',
                            options: [
                                {
                                    optionVal: 'ENTITIES',
                                    pathKey: 'multipleEvents',
                                    pageTypeChanges: [
                                        {
                                            page: 'EVENT',
                                            pageNameKey: 'myEvents',
                                        },
                                        {
                                            page: 'PARTY',
                                            pageNameKey: 'myParties',
                                        },
                                    ],
                                },
                                {
                                    optionVal: 'ENTITY',
                                    pathKey: 'oneEvent',
                                    pageTypeChanges: [
                                        {
                                            page: 'EVENT',
                                            pageNameKey: 'myEvent',
                                        },
                                        {
                                            page: 'PARTY',
                                            pageNameKey: 'myParty',
                                        },
                                    ],
                                },
                            ],
                        },
                    },
                },
                {
                    val: 'EVENT_TYPE',
                    categories: {
                        eventType: {
                            radio: true,
                            ignoreHeader: true,
                            actsOn: 'bundlePages',
                            options: [
                                {
                                    optionVal: 'party',
                                    pageVal: 'PARTY',
                                },
                                {
                                    optionVal: 'genericEvent',
                                    pageVal: 'EVENT',
                                },
                            ],
                        },
                    },
                },
                {
                    val: 'EVENT_EXTRAS',
                    optional: true,
                    categories: {
                        eventExtras: {
                            ignoreHeader: true,
                            actsOn: 'bundlePages',
                            options: [
                                {
                                    optionVal: 'eventPeople',
                                    pageVal: 'EVENT_PERSON',
                                },
                                {
                                    optionVal: 'eventVenues',
                                    pageVal: 'EVENT_VENUE',
                                },
                                {
                                    optionVal: 'eventSuppliers',
                                    pageVal: 'EVENT_SUPPLIER',
                                },
                            ],
                        },
                    },
                },
            ],
        },
        refinement: {
            done: false,
            customizations: [
                {
                    customizationVal: 'EVENT_NUMBER',
                    categoryKey: 'eventNumber',
                    selected: 'ENTITIES',
                },
                {
                    customizationVal: 'EVENT_TYPE',
                    categoryKey: 'eventType',
                    selected: 'genericEvent',
                },
                {
                    customizationVal: 'EVENT_EXTRAS',
                    categoryKey: 'eventExtras',
                    selected: [],
                },
            ],
        },
    },

    // Universal
    // BLANK: {
    //     val: 'BLANK',
    //     searchTerms: [
    //         'blank',
    //         'free',
    //         'empty',
    //     ],
    //     categories: ['universal'],
    //     initial: true,
    //     bundle: 'blank',
    // },
    GENERIC_ITEM: {
        val: 'GENERIC_ITEM',
        collab: true,
        searchTerms: [
            'item',
            'generic',
        ],
        categories: ['universal'],
        initial: true,
        bundle: 'starterItem',
    },
    GENERIC_PERSON: {
        val: 'GENERIC_PERSON',
        collab: true,
        searchTerms: [
            'person',
            'people',
            'generic',
        ],
        categories: ['universal'],
        initial: true,
        bundle: 'starterPerson',
    },
    // IDEAS: {
    //     val: 'IDEAS',
    //     collab: true,
    //     searchTerms: [
    //         'thoughts',
    //         'ideas',
    //     ],
    //     bundle: 'ideas',
    //     categories: ['universal'],
    // },
    SOCIAL_MEDIA_PRESENCE: {
        val: 'SOCIAL_MEDIA_PRESENCE',
        collab: true,
        searchTerms: [
            'socialmedia',
            'instagram',
            'linkedin',
            'twitter',
            'facebook',
            'influence',
        ],
        categories: ['universal'],
        bundle: 'socialMediaPlanning',
    },
    TODOS: {
        val: 'TODOS',
        collab: true,
        searchTerms: [
            'todos',
            'tasks',
            'planning',
        ],
        ignoreIfAlsoSelected: ['CRM_LIFE'],
        bundle: 'genericTodos',
        categories: ['universal'],
        initial: true,
    },
    CALENDAR: {
        val: 'CALENDAR',
        collab: true,
        searchTerms: [
            'calendar',
            'schedule',
            'meetings',
        ],
        ignoreIfAlsoSelected: ['CRM_LIFE'],
        bundle: 'genericCalendar',
        categories: ['universal'],
        initial: true,
    },
};

export default uses;
