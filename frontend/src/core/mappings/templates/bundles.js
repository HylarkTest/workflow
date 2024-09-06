import { allPages, makeEntity } from '@/core/mappings/templates/pages.js';

export const pagesList = {
    business: [
        {
            ...allPages.meetings,
            alwaysInclude: true,
            folder: 'Work and business',
        },
    ],
    work: [
        {
            ...allPages.meetings,
            alwaysInclude: true,
            folder: 'Work and business',
        },
    ],
    clients: [
        {
            ...allPages.clientPerson,
            folder: 'Work and business',
        },
        {
            ...allPages.clientOrganization,
            folder: 'Work and business',
        },
        {
            ...allPages.clientGeneric,
            folder: 'Work and business',
        },
    ],
    crmLife: [
        {
            ...allPages.genericTodos,
            alwaysInclude: true,
        },
        {
            ...allPages.genericCalendar,
            alwaysInclude: true,
        },
        {
            ...allPages.personPersonal,
            alwaysInclude: true,
        },
        {
            ...allPages.connectionsTodos,
            alwaysInclude: true,
        },
    ],
    subscriptions: [
        allPages.subscription,
    ],
    jobSearch: [
        {
            ...allPages.careerContact,
            folder: 'Job search',
        },
        {
            ...allPages.jobPosition,
            folder: 'Job search',
        },
        {
            ...allPages.targetCompany,
            folder: 'Job search',
        },
        {
            ...allPages.careerDocuments,
            folder: 'Job search',
        },
    ],
    executiveJobSearch: [
        {
            ...allPages.careerContact,
            folder: 'Job search',
        },
        {
            ...allPages.refereeSubset,
            folder: 'Job search',
        },
        {
            ...allPages.headhunterSubset,
            folder: 'Job search',
        },
        {
            ...allPages.execJobPosition,
            folder: 'Job search',
        },
        {
            ...allPages.targetCompany,
            folder: 'Job search',
        },
        {
            ...allPages.careerDocuments,
            folder: 'Job search',
        },
    ],
    products: [
        {
            ...allPages.professionalProduct,
            folder: 'Work and business',
        },
        {
            ...allPages.singleProduct,
            folder: 'Work and business',
        },
        allPages.productInspiration,
        allPages.productResources,
    ],
    educationSearch: [
        {
            ...allPages.educationInstitution,
            folder: 'Education search',
        },
        {
            ...allPages.educationProgram,
            folder: 'Education search',
        },
        {
            ...allPages.scholarship,
            folder: 'Education search',
        },
    ],
    furtherEducation: [
        {
            ...allPages.furtherEducationCourse,
            folder: 'Further education',
        },
        {
            ...allPages.furtherEducationCalendar,
            folder: 'Further education',
        },
    ],
    networking: [
        {
            ...allPages.networkContact,
            folder: 'Networking',
        },
        {
            ...allPages.networkOrganization,
            folder: 'Networking',
        },
        {
            ...allPages.networkCalendar,
            folder: 'Networking',
        },
    ],
    childrensSchedule: [
        {
            ...allPages.childrensCalendar,
            folder: 'Family schedule',
        },
        {
            ...allPages.childrensActivity,
            folder: 'Family schedule',
        },
        {
            ...allPages.child,
            folder: 'Family schedule',
        },
    ],
    coaching: [
        {
            ...allPages.coachingStudent,
            useKey: 'student',
            folder: 'Coaching',
        },
        {
            ...allPages.class,
            useKey: 'course',
            folder: 'Coaching',
        },
        {
            ...allPages.coachingCalendar,
            alwaysInclude: true,
            folder: 'Coaching',
        },
        {
            ...allPages.teachingAssignment,
            useKey: 'assignment',
            folder: 'Coaching',
        },
    ],
    tutoring: [
        {
            ...allPages.tutoringStudent,
            useKey: 'student',
            folder: 'Tutoring',
        },
        {
            ...allPages.tutoringCalendar,
            alwaysInclude: true,
            folder: 'Tutoring',
        },
        {
            ...allPages.class,
            useKey: 'course',
            folder: 'Tutoring',
        },
        {
            ...allPages.teachingAssignment,
            useKey: 'assignment',
            folder: 'Tutoring',
        },
    ],
    teaching: [
        {
            ...allPages.teachingCalendar,
            alwaysInclude: true,
            folder: 'Teaching',
        },
        {
            ...allPages.teachingAssignment,
            useKey: 'assignment',
            folder: 'Teaching',
        },
        {
            ...allPages.classStudent,
            useKey: 'student',
            folder: 'Teaching',
        },
        {
            ...allPages.class,
            useKey: 'course',
            folder: 'Teaching',
        },
    ],
    courseManagement: [
        {
            ...allPages.coursework,
            folder: 'Course management',
        },
        {
            ...allPages.educationCourse,
            folder: 'Course management',
        },
        {
            ...allPages.educationPerson,
            folder: 'Course management',
        },
        {
            ...allPages.extracurricular,
            folder: 'Course management',
        },
    ],
    writing: [
        {
            ...allPages.story,
            folder: 'Writing',
        },
        {
            ...allPages.storyCharacter,
            folder: 'Writing',
        },
        {
            ...allPages.storySetting,
            folder: 'Writing',
        },
        {
            ...allPages.publisher,
            folder: 'Writing',
        },
    ],
    writingNonFiction: [
        {
            ...allPages.nonFictionBook,
            folder: 'Writing',
        },
        {
            ...allPages.publisher,
            folder: 'Writing',
        },
    ],
    travel: [
        {
            ...allPages.destination,
            folder: 'Travel',
        },
        {
            ...allPages.travelActivity,
            folder: 'Travel',
        },
        {
            ...allPages.accommodation,
            folder: 'Travel',
        },
    ],
    weddingPlanning: [
        {
            ...makeEntity(allPages.wedding, 'myWedding'),
            alwaysInclude: true,
            folder: 'My wedding',
        },
        {
            ...allPages.weddingGuestSubset,
            folder: 'My wedding',
        },
        {
            ...allPages.weddingService,
            folder: 'My wedding',
        },
        {
            ...allPages.weddingVenue,
            folder: 'My wedding',
        },
        allPages.person,
    ],
    weddingsOrganizing: [
        {
            ...allPages.wedding,
            folder: 'Weddings',
        },
        {
            ...allPages.clients,
            folder: 'Weddings',
        },
        {
            ...allPages.vendor,
            folder: 'Weddings',
        },
    ],
    activity: [
        allPages.activity,
        allPages.activityInspiration,
        allPages.activityResources,
    ],
    applicantTracker: [
        {
            ...allPages.hiringCalendar,
            folder: 'Hiring',
        },
        {
            ...allPages.applicant,
            folder: 'Hiring',
        },
        {
            ...allPages.openPosition,
            folder: 'Hiring',
        },

    ],
    businessRoadmap: [
        {
            ...allPages.businessGoals,
            folder: 'Business',
        },
    ],
    contractorFinder: [
        {
            ...allPages.contractor,
            folder: 'Contractors',
        },
    ],
    contractorTimeTracker: [
        {
            ...allPages.contractorCalendar,
            folder: 'Contractors',
        },
        {
            ...allPages.contractor,
            folder: 'Contractors',
        },
        {
            ...allPages.contractorProjects,
            folder: 'Contractors',
        },
    ],
    employeeList: [
        {
            ...allPages.employee,
            folder: 'Employees',
        },
    ],
    staffSchedule: [
        {
            ...allPages.employee,
            folder: 'Staff',
        },
        {
            ...allPages.staffSchedule,
            folder: 'Staff',
        },
    ],
    bills: [
        {
            ...allPages.bill,
            folder: 'Household',
        },
        {
            ...allPages.billsSchedule,
            folder: 'Household',
        },
        // {
        //     ...allPages.serviceProviders,
        //     folder: 'Household',
        // },
    ],
    suppliers: [
        {
            ...allPages.supplier,
            folder: 'Suppliers',
        },
    ],
    projectManagement: [
        {
            ...allPages.workCalendar,
            alwaysInclude: true,
            folder: 'Work and business',
        },
        {
            ...allPages.professionalProject,
            folder: 'Work and business',
        },
        {
            ...allPages.singleProject,
            folder: 'Work and business',
        },
    ],
    design: [
        {
            ...allPages.design,
            folder: 'Design',
        },
        {
            ...allPages.client,
            folder: 'Design',
        },
        allPages.designInspiration,
        allPages.designResources,
    ],
    // ideas: [
    //     allPages.idea,
    // ],
    photographyBusiness: [
        {
            ...allPages.photographyCalendar,
            folder: 'Photography',
        },
        {
            ...allPages.client,
            folder: 'Photography',
        },
        allPages.photographyInspiration,
        allPages.photographyResources,
    ],
    bucketList: [
        allPages.bucketList,
    ],
    documentRenewals: [
        {
            ...allPages.documents,
            folder: 'Household',
        },
        {
            ...allPages.documentsSchedule,
            folder: 'Household',
        },
    ],
    gardening: [
        {
            ...allPages.gardeningProject,
            folder: 'Household',
        },
        {
            ...allPages.gardeningCalendar,
            folder: 'Household',
        },
        allPages.gardeningInspiration,
        allPages.gardeningResources,
    ],
    gifts: [
        {
            ...allPages.gift,
            folder: 'Gifts',
        },
        allPages.giftBoard,
        allPages.giftLinks,
    ],
    // giftIdeas: [
    //     allPages.person,
    //     {
    //         ...allPages.gift,
    //         folder: 'Gifts',
    //     },
    // ],
    // holidayGifts: [
    //     allPages.person,
    //     {
    //         ...allPages.gift,
    //         folder: 'Gifts',
    //     },
    //     {
    //         ...allPages.giftReceiverSubset,
    //         folder: 'Gifts',
    //     },
    // ],
    homeImprovement: [
        {
            ...allPages.homeImprovementProject,
            folder: 'Household',
        },
        allPages.homeInspiration,
        allPages.homeResources,
    ],
    vehiclePlanner: [
        {
            ...allPages.vehicleSchedule,
            folder: 'Household',
        },
        {
            ...allPages.vehicle,
            folder: 'Household',
        },
    ],
    personalCrm: [
        allPages.personPersonal,
    ],
    petCare: [
        {
            ...allPages.pet,
            folder: 'Household',
        },
    ],
    recipes: [
        {
            ...allPages.recipe,
            folder: 'Food',
        },
        {
            ...allPages.recipeLinks,
            folder: 'Food',
        },
    ],
    mealPrep: [
        {
            ...allPages.mealCalendar,
            folder: 'Food',
        },
        {
            ...allPages.recipe,
            folder: 'Food',
        },
        {
            ...allPages.recipeLinks,
            folder: 'Food',
        },
    ],
    hobbiesArt: [
        {
            ...allPages.hobbiesArt,
            folder: 'Hobbies',
        },
        allPages.artInspiration,
        allPages.artResources,
    ],
    hobbiesCollecting: [
        {
            ...allPages.hobbiesCollection,
            folder: 'Hobbies',
        },
        allPages.collectingInspiration,
        allPages.collectingResources,
    ],
    // hobbiesGaming: [
    //     {
    //         ...allPages.hobbiesGame,
    //         folder: 'Hobbies',
    //     },
    // ],
    jewelryMaking: [
        {
            ...allPages.jewelryMaking,
            folder: 'Hobbies',
        },
        allPages.jewelryInspiration,
        allPages.jewelryResources,
    ],
    knitting: [
        {
            ...allPages.knitting,
            folder: 'Hobbies',
        },
        allPages.knittingInspiration,
        allPages.knittingResources,
    ],
    photographyHobby: [
        {
            ...allPages.photographyHobby,
            folder: 'Hobbies',
        },
        allPages.photographyInspiration,
        allPages.photographyResources,
    ],
    hobbiesProjects: [
        {
            ...allPages.hobbiesProject,
            folder: 'Hobbies',
        },
        allPages.hobbiesInspiration,
        allPages.hobbiesResources,
    ],
    reading: [
        {
            ...allPages.hobbiesBook,
            folder: 'Hobbies',
        },
    ],
    sewing: [
        {
            ...allPages.sewing,
            folder: 'Hobbies',
        },
        allPages.sewingInspiration,
        allPages.sewingResources,
    ],
    woodworking: [
        {
            ...allPages.woodworking,
            folder: 'Hobbies',
        },
        allPages.woodworkingInspiration,
        allPages.woodworkingResources,
    ],
    worldBuilding: [
        {
            ...allPages.settings,
            folder: 'World-building',
        },
        {
            ...allPages.characters,
            folder: 'World-building',
        },
        {
            ...allPages.worldBuildingIdeas,
            folder: 'World-building',
        },
        allPages.worldBuildingInspiration,
        allPages.worldBuildingResources,
    ],
    events: [
        {
            ...allPages.event,
            folder: 'Event planning',
        },
        {
            ...allPages.party,
            folder: 'Event planning',
        },
        {
            ...allPages.eventPerson,
            folder: 'Event planning',
        },
        {
            ...allPages.eventSupplier,
            folder: 'Event planning',
        },
        {
            ...allPages.eventVenues,
            folder: 'Event planning',
        },
        allPages.eventInspiration,
        allPages.eventResources,
    ],
    socialMediaPlanning: [
        {
            ...allPages.postingSchedule,
            folder: 'Social media',
        },
        {
            ...allPages.socialMediaPost,
            folder: 'Social media',
        },
        allPages.socialMediaInspiration,
        allPages.socialMediaResources,
    ],
    starterItem: [
        allPages.starterItem,
    ],
    starterPerson: [
        allPages.person,
    ],
    genericTodos: [
        allPages.genericTodos,
    ],
    genericCalendar: [
        allPages.genericCalendar,
    ],
};

export const dontFitYet = {
    vehicleSearch: [
        {
            ...allPages.potentialVehicle,
            folder: 'Vehicle search',
        },
    ],
    homeBuying: [
        {
            ...allPages.viewingsCalendar,
            folder: 'Home buying',
        },
        {
            ...allPages.potentialHome,
            folder: 'Home buying',
        },
    ],
    rentalSearch: [
        {
            ...allPages.potentialRentalHome,
            folder: 'Rental search',
        },
        {
            ...allPages.viewingsCalendar,
            folder: 'Rental search',
        },
    ],
    singleProjectManagement: [
        {
            ...allPages.professionalProject,
            folder: 'My project',
        },
    ],
    crm: [
        {
            ...allPages.professionalContacts,
            folder: 'CRM',
        },
        {
            ...allPages.meetings,
            folder: 'CRM',
        },
    ],
    competitorTracking: [
        {
            ...allPages.competitorResources,
            folder: 'Competitors',
        },
        {
            ...allPages.competitor,
            folder: 'Competitors',
        },
    ],
    productRoadmap: [
        {
            ...allPages.singleProduct,
            folder: 'Work and business',
        },
        {
            ...allPages.productTasks,
            folder: 'Work and business',
        },
        {
            ...allPages.goals,
            folder: 'Work and business',
        },
    ],
    productPlanning: [
        {
            ...allPages.product,
            folder: 'Work and business',
        },
        {
            ...allPages.features,
            folder: 'Work and business',
        },
        {
            ...allPages.goals,
            folder: 'Work and business',
        },
    ],
    blogPlanning: [
        {
            ...allPages.blogSchedule,
            folder: 'Blog',
        },
        {
            ...allPages.blog,
            folder: 'Blog',
        },
        allPages.blogResources,
    ],
    contentCreation: [
        {
            ...allPages.content,
            folder: 'Content creation',
        },
        {
            ...allPages.collaborator,
            folder: 'Content creation',
        },
        {
            ...allPages.contentCalendar,
            folder: 'Content creation',
        },
        {
            ...allPages.contentSchedule,
            folder: 'Content creation',
        },
        allPages.contentInspiration,
        allPages.contentResources,
    ],
    jobPlacements: [
        {
            ...allPages.clients,
            folder: 'Job placements',
        },
        {
            ...allPages.openJobPositions,
            folder: 'Job placements',
        },
        {
            ...allPages.candidates,
            folder: 'Job placements',
        },
    ],
    propertyManagement: [
        {
            ...allPages.contractors,
            folder: 'Property management',
        },
        {
            ...allPages.properties,
            folder: 'Property management',
        },
        {
            ...allPages.tenants,
            folder: 'Property management',
        },
        {
            ...allPages.requiredPropertyWork,
            folder: 'Property management',
        },
    ],
    salesAndRentals: [
        {
            ...allPages.viewingsCalendar,
            folder: 'Properties',
        },
        {
            ...allPages.availableProperties,
            folder: 'Properties',
        },
        {
            ...allPages.interestedParties,
            folder: 'Properties',
        },
    ],
    bugTracker: [
        {
            ...allPages.ticket,
            folder: 'Software development',
        },
        {
            ...allPages.bugTicketSubset,
            folder: 'Software development',
        },
        {
            ...allPages.featureTicketSubset,
            folder: 'Software development',
        },
    ],
};

export default { pagesList };
