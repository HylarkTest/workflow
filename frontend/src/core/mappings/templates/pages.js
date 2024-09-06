import { warn } from 'vue';

import * as features from '@/core/mappings/templates/features.js';

import { getNewFields } from '@/core/mappings/templates/newFields.js';
import { getPageFields } from '@/core/mappings/templates/basics.js';
import { getRelationship } from '@/core/mappings/templates/relationships.js';
import { getSubset } from '@/core/mappings/templates/entitySubsets.js';

import { $t } from '@/i18n.js';

// PLEASE look at documentation.js in this folder
// for the structure and what everything is

// Id needs to be defined when used in the specific page when a part of a page that
// can be reused and merged.

function getFieldsValuesNames(options) {
    const values = options.values;
    const langPath = options.langPath;
    return _.mapValues(values, (val) => {
        if (langPath) {
            return $t(`${langPath}.${_.camelCase(val.key)}`, val.params);
        }
        return $t(`labels.${_.camelCase(val)}`);
    });
}

export function getFieldsDetails(fieldsArr) {
    return fieldsArr.map((field) => {
        if (field) {
            const nameVal = field?.nameKey || field?.id;

            if (!nameVal) {
                warn(`Check that the field with type "${field.type}"
                    has an id, and also a nameKey if required`);
            }

            const nameKey = _.camelCase(nameVal);
            const newField = {
                ...field,
                name: $t(`labels.${nameKey}`),
            };

            if (field.options) {
                const newOptions = {
                    ...field.options,
                };

                // For subfields
                if (field.options.fields) {
                    newOptions.fields = getFieldsDetails(field.options.fields);
                }

                // For dropdown or other values
                if (field.options.values) {
                    newOptions.values = getFieldsValuesNames(field.options);
                }

                newField.options = newOptions;
            }

            return newField;
        }
        return null;
    });
}

function getDescription(pageType, id) {
    const camelPageType = _.camelCase(pageType);
    const descriptionString = $t(`defaultPages.${camelPageType}.${_.camelCase(id)}`);
    // Check in case there is no translation
    return descriptionString.includes('defaultPages') ? '' : descriptionString;
}

// This function provides some of the necessary details.
// Especially relevant for entities pages.
function getPageBasics(symbol, id, extras, pageType = 'ENTITIES') {
    // Extras has fields and markerGroups

    const description = getDescription(pageType, id);

    const basicObj = {
        symbol,
        pageType,
        id,
        description,
    };

    const hasExtras = !!extras;
    if (hasExtras) {
        // Fields mandatory but markerGroups optional
        const fields = extras.fields;

        basicObj.fields = getFieldsDetails(fields);
        basicObj.features = extras.features || features.infoFeatures;
        basicObj.type = extras.type || 'ITEM';

        const extraVals = [
            'markerGroups',
            'relationships',
        ];

        extraVals.forEach((objKey) => {
            const keyVal = extras[objKey];

            if (keyVal) {
                basicObj[objKey] = keyVal;
            }
        });

        if (!basicObj.fields?.length) {
            warn(`This entities page with id "${id}" does not have any fields. Add fields.`);
        }

        const dataObj = {
            fields: basicObj.fields,
        };

        if (basicObj.markerGroups) {
            dataObj.markerGroups = basicObj.markerGroups;
        }
        // View and data defaults are set in template helpers due
        // to certain values being dependent on the other pages present
        basicObj.newFields = getNewFields(id, dataObj);
    }

    return basicObj;
}

// This function deals with adjustments to the pages
// depending on the context of the other pages
// are in the same template
function getPageWithSpecifics(page, specificsObj, id) {
    const newPage = _.cloneDeep(page);

    if (specificsObj) {
        const specificKeys = [
            'symbol',
            'pageName',
            'name',
            'singularName',
            'relationships',
            'markerGroups',
            'pageType',
            'templateRefs',
            'altPageName',
            'includeInPages',
        ];

        specificKeys.forEach((objKey) => {
            const keyVal = specificsObj[objKey];

            if (_.has(specificsObj, objKey)) {
                newPage[objKey] = keyVal;
            }
        });

        const fields = specificsObj.fields;
        if (fields) {
            newPage.fields = getFieldsDetails(fields);
        }
    }

    if (id) {
        newPage.id = id;
        newPage.description = getDescription(newPage.pageType, id);
    }

    return newPage;
}

function generatePageNameObj(nameArr) {
    // 0 - name
    // 1 - singular name
    // 2 - page name
    const obj = {
        name: nameArr[0],
    };
    if (nameArr[1]) {
        obj.singularName = nameArr[1];
    }
    if (nameArr[2]) {
        obj.pageName = nameArr[2];
    }
    return obj;
}

export function getPageNames(nameSource) {
    let nameObj = nameSource;
    if (_.isArray(nameSource)) {
        nameObj = generatePageNameObj(nameSource);
    }
    const formattedNames = {};
    // Blueprint plural OR page name if no blueprint
    if (nameObj.name) {
        formattedNames.name = $t(`labels.${nameObj.name}`);
    }

    // Blueprint singular
    if (nameObj.singularName) {
        formattedNames.singularName = $t(`labels.${nameObj.singularName}`);
    }

    // Page name
    if (nameObj.pageName) {
        formattedNames.pageName = $t(`labels.${nameObj.pageName}`);
    }

    return formattedNames;
}

export function makeEntity(page, pageNameKey) {
    return {
        ...page,
        pageType: 'ENTITY',
        ...getPageNames({ pageName: pageNameKey }),
    };
}

export const featurePages = {
    // Document pages
    careerDocuments: {
        ...getPageNames({ pageName: 'careerDocuments' }),
        ...getPageBasics(
            'fa-folders',
            'CAREER_DOCUMENTS',
            null,
            'DOCUMENTS'
        ),
        includeInPages: true,
        lists: ['CAREER_DOCUMENTS'],
        templateRefs: ['CAREER_DOCUMENTS'],
    },

    // Calendar pages
    tutoringCalendar: {
        ...getPageNames({ pageName: 'tutoringCalendar' }),
        ...getPageBasics(
            'fa-calendar-users',
            'TUTORING_CALENDAR',
            null,
            'CALENDAR'
        ),
        includeInPages: true,
        lists: ['TUTORING_SCHEDULE'],
        templateRefs: ['TUTORING_CALENDAR'],
    },
    coachingCalendar: {
        ...getPageNames({ pageName: 'coachingCalendar' }),
        ...getPageBasics(
            'fa-hockey-stick-puck',
            'COACHING_CALENDAR',
            null,
            'CALENDAR'
        ),
        includeInPages: true,
        lists: ['COACHING_SCHEDULE'],
        templateRefs: ['COACHING_CALENDAR'],
    },
    teachingCalendar: {
        ...getPageNames({ pageName: 'teachingCalendar' }),
        ...getPageBasics(
            'fa-calendar-users',
            'TEACHING_CALENDAR',
            null,
            'CALENDAR'
        ),
        includeInPages: true,
        lists: ['TEACHING_SCHEDULE'],
        templateRefs: ['TEACHING_CALENDAR'],
    },
    networkCalendar: {
        ...getPageNames({ pageName: 'networkCalendar' }),
        ...getPageBasics(
            'fa-globe',
            'NETWORK_CALENDAR',
            null,
            'CALENDAR'
        ),
        includeInPages: true,
        lists: ['NETWORK_EVENTS'],
        templateRefs: ['NETWORK_CALENDAR'],
    },
    furtherEducationCalendar: {
        ...getPageNames({ pageName: 'furtherEducationCalendar' }),
        ...getPageBasics(
            'fa-diploma',
            'FURTHER_EDUCATION_CALENDAR',
            null,
            'CALENDAR'
        ),
        lists: ['FURTHER_EDUCATION_EVENTS'],
        templateRefs: ['FURTHER_EDUCATION_CALENDAR'],
    },
    hiringCalendar: {
        ...getPageNames({ pageName: 'hiringCalendar' }),
        ...getPageBasics(
            'fa-calendar-circle-user',
            'HIRING_CALENDAR',
            null,
            'CALENDAR'
        ),
        includeInPages: true,
        lists: ['INTERVIEWS'],
        templateRefs: ['HIRING_CALENDAR'],
    },
    childrensCalendar: {
        ...getPageNames({ pageName: 'childrensCalendar' }),
        ...getPageBasics(
            'fa-child',
            'CHILDRENS_CALENDAR',
            null,
            'CALENDAR'
        ),
        includeInPages: true,
        lists: ['CHILDRENS_CALENDAR'],
        templateRefs: ['CHILDRENS_CALENDAR'],
    },
    meetings: {
        ...getPageNames({ pageName: 'meetings' }),
        ...getPageBasics(
            'fa-calendar-users',
            'MEETINGS',
            null,
            'CALENDAR'
        ),
        includeInPages: true,
        lists: ['MEETINGS'],
        templateRefs: ['MEETINGS'],
    },
    viewingsCalendar: {
        ...getPageNames({ pageName: 'propertiesCalendar' }),
        ...getPageBasics(
            'fa-house-chimney',
            'VIEWINGS_CALENDAR',
            null,
            'CALENDAR'
        ),
        lists: ['VIEWINGS_CALENDAR'],
        templateRefs: ['VIEWINGS_CALENDAR'],
    },
    workCalendar: {
        ...getPageNames({ pageName: 'workCalendar' }),
        ...getPageBasics(
            'fa-car-building',
            'WORK_CALENDAR',
            null,
            'CALENDAR'
        ),
        includeInPages: true,
        lists: ['WORK_CALENDAR'],
        templateRefs: ['WORK_CALENDAR'],
        mergeListsIds: ['WORK_CALENDAR'],
    },
    contractorCalendar: {
        ...getPageNames({ pageName: 'contractorCalendar' }),
        ...getPageBasics(
            'fa-hammer',
            'MEETINGS',
            null,
            'CALENDAR'
        ),
        lists: ['CONTRACTOR_CALENDAR'],
        templateRefs: ['CONTRACTOR_CALENDAR'],
    },
    staffSchedule: {
        ...getPageNames({ pageName: 'staffSchedule' }),
        ...getPageBasics(
            'fa-users-rectangle',
            'STAFF_SCHEDULE',
            null,
            'CALENDAR'
        ),
        includeInPages: true,
        lists: ['STAFF_SCHEDULE'],
        templateRefs: ['STAFF_SCHEDULE'],
    },
    mealCalendar: {
        ...getPageNames({ pageName: 'mealsCalendar' }),
        ...getPageBasics(
            'fa-face-smile-tongue',
            'MEAL_CALENDAR',
            null,
            'CALENDAR'
        ),
        lists: ['MEAL_CALENDAR'],
        templateRefs: ['MEAL_CALENDAR'],
    },
    gardeningCalendar: {
        ...getPageNames({ pageName: 'gardeningCalendar' }),
        ...getPageBasics(
            'fa-seedling',
            'GARDENING_CALENDAR',
            null,
            'CALENDAR'
        ),
        lists: ['GARDENING_CALENDAR'],
        templateRefs: ['GARDENING_CALENDAR'],
    },
    genericCalendar: {
        ...getPageNames({ pageName: 'genericCalendars' }),
        ...getPageBasics(
            'fa-calendar-heart',
            'GENERIC_CALENDAR',
            null,
            'CALENDAR'
        ),
        lists: [
            'PERSONAL_EVENTS',
            'FAMILY',
            'SOCIAL',
            'LIFE_ADMIN',
            'APPOINTMENTS',
            'BIRTHDAYS',
        ],
        includeInPages: true,
        templateRefs: ['GENERIC_CALENDAR'],
    },

    // Links
    resources: {
        ...getPageNames({ pageName: 'resources' }),
        ...getPageBasics(
            'fa-book-sparkles',
            'RESOURCES',
            null,
            'LINKS'
        ),
        alwaysInclude: true,
        lists: ['RESOURCES'],
        templateRefs: ['RESOURCES'],
        mergeListsIds: ['RESOURCES'],
        includeInPages: true,
    },
    recipeLinks: {
        ...getPageNames({ pageName: 'recipeLinks' }),
        ...getPageBasics(
            'fa-plate-utensils',
            'RECIPE_LINKS',
            null,
            'LINKS'
        ),
        lists: ['RECIPE_LINKS'],
        templateRefs: ['RECIPE_LINKS'],
        includeInPages: true,
    },
    competitorResources: {
        ...getPageNames({ pageName: 'competitorLinks' }),
        ...getPageBasics(
            'fa-person-running-fast',
            'COMPETITOR_RESOURCES',
            null,
            'LINKS'
        ),
        lists: ['COMPETITOR_RESOURCES'],
        templateRefs: ['COMPETITOR_RESOURCES'],
    },
    giftLinks: {
        ...getPageNames({ pageName: 'giftLinks' }),
        ...getPageBasics(
            'fa-hand-holding-heart',
            'GIFT_LINKS',
            null,
            'LINKS'
        ),
        lists: ['GIFT_LINKS'],
        templateRefs: ['GIFT_LINKS'],
        includeInPages: true,
    },

    // Pinboard
    inspiration: {
        ...getPageNames({ pageName: 'inspiration' }),
        ...getPageBasics(
            'fa-hexagon-image',
            'INSPIRATION',
            null,
            'PINBOARD'
        ),
        alwaysInclude: true,
        lists: ['INSPIRATION'],
        templateRefs: ['INSPIRATION'],
        mergeListsIds: ['INSPIRATION'],
        includeInPages: true,
    },
    giftBoard: {
        ...getPageNames({ pageName: 'giftBoard' }),
        ...getPageBasics(
            'fa-gifts',
            'GIFT_BOARD',
            null,
            'PINBOARD'
        ),
        lists: ['GIFT_BOARD'],
        templateRefs: ['GIFT_BOARD'],
        includeInPages: true,
    },

    // Todos
    contentTodos: {
        ...getPageNames({ pageName: 'contentTodos' }),
        ...getPageBasics(
            'fa-keyboard',
            'CONTENT_TODOS',
            null,
            'TODOS'
        ),
        lists: ['CONTENT_TODOS'],
        templateRefs: ['CONTENT_TODOS'],
        mergeListsIds: ['CONTENT_TODOS'],
    },
    homeTodos: {
        ...getPageNames({ pageName: 'homeTodos' }),
        ...getPageBasics(
            'fa-house-window',
            'HOME_TODOS',
            null,
            'TODOS'
        ),
        lists: ['HOME', 'GARDEN', 'PETS'],
        templateRefs: ['HOME_TODOS'],
        mergeListsIds: ['HOME_TODOS'],
        includeInPages: true,
    },
    genericTodos: {
        ...getPageNames({ pageName: 'genericTodos' }),
        ...getPageBasics(
            'fa-shield-check',
            'GENERIC_TODOS',
            null,
            'TODOS'
        ),
        lists: [
            'PERSONAL_TODOS',
            'HOME',
            'PRIORITIES',
            'ERRANDS',
        ],
        templateRefs: ['GENERIC_TODOS'],
        includeInPages: true,
    },
    connectionsTodos: {
        ...getPageNames({ pageName: 'socialCircleTodos' }),
        ...getPageBasics(
            'fa-mailbox',
            'CONNECTIONS_TODOS',
            null,
            'TODOS'
        ),
        lists: [
            'SOCIAL_CIRCLE',
        ],
        includeInPages: true,
        templateRefs: ['CONNECTIONS_TODOS'],
    },
};

// These ones just add lists to the main feature pages from the array above, featurePages
// These should not be included as their own distinct pages at this time.
export const specificFeaturePages = {
    // Calendar
    photographyCalendar: {
        ...featurePages.workCalendar,
        includeInPages: false,
        lists: ['PHOTOGRAPHY_CALENDAR'],
    },
    contentCalendar: {
        ...featurePages.workCalendar,
        includeInPages: false,
        lists: ['CONTENT_CALENDAR'],
    },

    // Links
    sewingResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['SEWING_RESOURCES'],
    },
    woodworkingResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['WOODWORKING_RESOURCES'],
    },
    knittingResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['KNITTING_RESOURCES'],
    },
    contentResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['CONTENT_RESOURCES'],
    },
    socialMediaResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['SOCIAL_MEDIA_RESOURCES'],
    },
    productResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['PRODUCT_RESOURCES'],
    },
    designResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['DESIGN_RESOURCES'],
    },
    photographyResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['PHOTOGRAPHY_RESOURCES'],
    },
    gardeningResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['GARDENING_RESOURCES'],
    },
    homeResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['HOME_RESOURCES'],
    },
    artResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['ART_RESOURCES'],
    },
    collectingResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['COLLECTING_RESOURCES'],
    },
    jewelryResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['JEWELRY_RESOURCES'],
    },
    hobbiesResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['HOBBIES_RESOURCES'],
    },
    worldBuildingResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['WORLD_BUILDING_RESOURCES'],
    },
    blogResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['BLOG_RESOURCES'],
    },
    eventResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['EVENT_RESOURCES'],
    },
    activityResources: {
        ...featurePages.resources,
        includeInPages: false,
        lists: ['ACTIVITY_RESOURCES'],
    },

    // Pinboard
    sewingInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['SEWING_INSPIRATION'],
    },
    woodworkingInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['WOODWORKING_INSPIRATION'],
    },
    knittingInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['KNITTING_INSPIRATION'],
    },
    contentInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['CONTENT_INSPIRATION'],
    },
    socialMediaInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['SOCIAL_MEDIA_INSPIRATION'],
    },
    productInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['PRODUCT_INSPIRATION'],
    },
    designInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['DESIGN_INSPIRATION'],
    },
    photographyInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['PHOTOGRAPHY_INSPIRATION'],
    },
    gardeningInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['GARDENING_INSPIRATION'],
    },
    homeInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['HOME_INSPIRATION'],
    },
    artInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['ART_INSPIRATION'],
    },
    collectingInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['COLLECTING_INSPIRATION'],
    },
    jewelryInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['JEWELRY_INSPIRATION'],
    },
    hobbiesInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['HOBBIES_INSPIRATION'],
    },
    worldBuildingInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['WORLD_BUILDING_INSPIRATION'],
    },
    eventInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['EVENTS_INSPIRATION'],
    },
    activityInspiration: {
        ...featurePages.inspiration,
        includeInPages: false,
        lists: ['ACTIVITIES_INSPIRATION'],
    },

    // TODOS
    contentSchedule: {
        ...featurePages.contentTodos,
        includeInPages: false,
        lists: ['CONTENT_SCHEDULE'],
    },
    blogSchedule: {
        ...featurePages.contentTodos,
        includeInPages: false,
        lists: ['BLOG_SCHEDULE'],
    },
    postingSchedule: {
        ...featurePages.contentTodos,
        includeInPages: false,
        lists: ['POSTING_SCHEDULE'],
    },
    vehicleSchedule: {
        ...featurePages.homeTodos,
        includeInPages: false,
        lists: ['VEHICLE_SCHEDULE'],
    },
    billsSchedule: {
        ...featurePages.homeTodos,
        includeInPages: false,
        lists: ['BILLS_SCHEDULE'],
    },
    documentsSchedule: {
        ...featurePages.homeTodos,
        includeInPages: false,
        lists: ['DOCUMENTS_SCHEDULE'],
    },
};

export const basePages = {
    // People
    person: {
        ...getPageNames(['people', 'person']),
        ...getPageBasics(
            'fa-people-simple',
            'PERSON',
            {
                fields: getPageFields('PERSON'),
                type: 'PERSON',
                features: features.mainWithEmail,
            }
        ),
        includeInPages: true,
        templateRefs: ['PERSON'],
        mergeIds: ['PERSON'],
    },
    careerContact: {
        ...getPageNames(['careerContacts', 'careerContact', 'careerNetwork']),
        ...getPageBasics(
            'fa-address-book',
            'CAREER_CONTACT',
            {
                fields: getPageFields('CAREER_CONTACT'),
                type: 'PERSON',
                features: features.mainWithEmail,
                markerGroups: ['CAREER_CONTACT_DESCRIPTOR_TAGS', 'CONTACTED_PIPELINE'],
            }
        ),
        includeInPages: true,
        altPageName: 'professionalContacts',
        templateRefs: ['CAREER_CONTACT'],
        mergeIds: ['PERSON'],
    },
    refereeSubset: {
        ...getPageNames({ pageName: 'referees' }),
        ...getPageBasics(
            'fa-person-circle-check',
            'REFEREE_SUBSET'
        ),
        subset: getSubset('REFEREE_SUBSET', 'CAREER_CONTACT'),
        specificDefaults: {
            MARKERS: {
                CAREER_CONTACT_DESCRIPTOR_TAGS: ['referee'],
            },
        },
    },
    headhunterSubset: {
        ...getPageNames({ pageName: 'headhunters' }),
        ...getPageBasics(
            'fa-screen-users',
            'HEADHUNTER_SUBSET'
        ),
        subset: getSubset('HEADHUNTER_SUBSET', 'CAREER_CONTACT'),
        specificDefaults: {
            MARKERS: {
                CAREER_CONTACT_DESCRIPTOR_TAGS: ['headhunter'],
            },
        },
    },
    weddingGuestSubset: {
        ...getPageNames({ pageName: 'weddingGuests' }),
        ...getPageBasics(
            'fa-screen-users',
            'WEDDING_GUEST_SUBSET',
            {
                fields: getPageFields('WEDDING_GUEST_SUBSET'),
            }
        ),
        subset: getSubset('WEDDING_GUEST_SUBSET', 'PERSON'),
        specificDefaults: {
            FIELDS: {
                IS_WEDDING_GUEST: true,
            },
        },
    },
    applicant: {
        ...getPageNames(['applicants', 'applicant']),
        ...getPageBasics(
            'fa-user-tie-hair-long',
            'APPLICANT',
            {
                fields: getPageFields('APPLICANT'),
                type: 'PERSON',
                features: features.getFeatures(features.mainWithEmail, ['FAVORITES']),
                markerGroups: ['APPLICANT_TAGS', 'APPLICANT_PIPELINE'],
            }
        ),
        includeInPages: true,
        templateRefs: ['APPLICANT'],
        mergeIds: ['PERSON'],
    },
    clientPerson: {
        ...getPageNames(['clientsPeople', 'clientsPerson']),
        ...getPageBasics(
            'fa-people-line',
            'CLIENT_PERSON',
            {
                fields: getPageFields('CLIENT_PERSON'),
                type: 'PERSON',
                features: features.getFeatures(features.mainWithEmail, ['FAVORITES']),
            }
        ),
        templateRefs: ['CLIENT_PERSON'],
        mergeIds: ['PERSON'],
    },
    pet: {
        ...getPageNames(['pets', 'pet']),
        ...getPageBasics(
            'fa-dog',
            'PET',
            {
                fields: getPageFields('PET'),
                features: features.infoFeatures,
            }
        ),
        includeInPages: true,
        templateRefs: ['PET'],
    },
    child: {
        ...getPageNames(['children', 'child']),
        ...getPageBasics(
            'fa-child-reaching',
            'CHILD',
            {
                fields: getPageFields('STUDENT'),
                type: 'PERSON',
                features: features.infoFeatures,
            }
        ),
        includeInPages: true,
        templateRefs: ['CHILD'],
        mergeIds: ['PERSON'],
    },

    // Student pages
    student: {
        ...getPageNames(['students', 'student']),
        ...getPageBasics(
            'fa-children',
            'STUDENT',
            {
                fields: getPageFields('STUDENT'),
                type: 'PERSON',
                features: features.mainWithEmail,
            }
        ),
        templateRefs: ['STUDENT'],
        mergeIds: ['STUDENT', 'PERSON'],
    },
    classStudent: {
        ...getPageNames(['classStudents', 'classStudent', 'myStudents']),
        ...getPageBasics(
            'fa-person-shelter',
            'CLASS_STUDENT',
            {
                fields: getPageFields('CLASS_STUDENT'),
                type: 'PERSON',
                features: features.mainWithEmail,
            }
        ),
        templateRefs: ['CLASS_STUDENT'],
        mergeIds: ['STUDENT', 'PERSON'],
    },

    // Travel and activities
    destination: {
        ...getPageNames(['destinations', 'destination']),
        ...getPageBasics(
            'fa-island-tropical',
            'DESTINATION',
            {
                fields: getPageFields('DESTINATION'),
                markerGroups: ['TRIP_TYPE_TAGS'],
                features: features.infoFeatures,
                relationships: [
                    getRelationship('activities', 'TRAVEL_ACTIVITY', 'trip', 'MANY_TO_ONE'),
                    getRelationship('accommodation', 'ACCOMODATION', 'trip', 'MANY_TO_ONE'),
                ],
            }
        ),
        includeInPages: true,
        templateRefs: ['DESTINATION'],
    },
    activity: {
        ...getPageNames(['activities', 'activity']),
        ...getPageBasics(
            'fa-bicycle',
            'ACTIVITY',
            {
                fields: getPageFields('ACTIVITY'),
                features: features.infoFeatures,
            }
        ),
        includeInPages: true,
        templateRefs: ['ACTIVITY'],
        mergeIds: ['ACTIVITY'],
    },
    childrensActivity: {
        ...getPageNames(['childrensActivities', 'childrensActivity']),
        ...getPageBasics(
            'fa-tennis-ball',
            'CHILDRENS_ACTIVITY',
            {
                fields: getPageFields('CHILDRENS_ACTIVITY'),
                features: features.infoFeatures,
            }
        ),
        includeInPages: true,
        templateRefs: ['CHILDRENS_ACTIVITY'],
    },
    travelActivity: {
        ...getPageNames(['travelActivities', 'travelActivity']),
        ...getPageBasics(
            'fa-umbrella-beach',
            'TRAVEL_ACTIVITY',
            {
                fields: getPageFields('TRAVEL_ACTIVITY'),
                features: features.infoFeatures,
            }
        ),
        includeInPages: true,
        templateRefs: ['TRAVEL_ACTIVITY'],
        mergeIds: ['ACTIVITY'],
    },
    extracurricular: {
        ...getPageNames(['extracurriculars', 'extracurricular']),
        ...getPageBasics(
            'fa-flag-pennant',
            'EXTRACURRICULAR',
            {
                fields: getPageFields('EXTRACURRICULAR'),
                features: features.infoFeatures,
            }
        ),
        templateRefs: ['EXTRACURRICULAR'],
    },
    event: {
        ...getPageNames(['events', 'event']),
        ...getPageBasics(
            'fa-presentation-screen',
            'EVENT',
            {
                fields: getPageFields('EVENT'),
                features: features.infoFeatures,
            }
        ),
        altPageName: 'events',
        includeInPages: true,
        singleOrMultiple: true,
        templateRefs: ['EVENT'],
        mergeIds: ['EVENT'],
    },
    wedding: {
        ...getPageNames(['weddings', 'wedding']),
        ...getPageBasics(
            'fa-rings-wedding',
            'WEDDING',
            {
                fields: getPageFields('WEDDING'),
                features: features.infoFeatures,
            }
        ),
        templateRefs: ['WEDDING'],
    },

    // Places
    accommodation: {
        ...getPageNames(['accommodations', 'accommodation']),
        ...getPageBasics(
            'fa-hotel',
            'ACCOMMODATION',
            {
                fields: getPageFields('ACCOMMODATION'),
                markerGroups: ['RATING_TAGS'],
                features: features.getFeatures(features.mainWithPrioritiesFavorites, ['EMAILS']),
            }
        ),
        includeInPages: true,
        templateRefs: ['ACCOMMODATION'],
    },
    venue: {
        ...getPageNames(['venues', 'venue']),
        ...getPageBasics(
            'fa-house-building',
            'VENUE',
            {
                fields: getPageFields('VENUE'),
                features: features.infoFeatures,
            }
        ),
        templateRefs: ['VENUE'],
    },

    // Organizations
    organization: {
        ...getPageNames(['organizations', 'organization']),
        ...getPageBasics(
            'fa-building',
            'ORGANIZATION',
            {
                fields: getPageFields('ORGANIZATION'),
                features: features.mainWithPrioritiesFavorites,
            }
        ),
        templateRefs: ['ORGANIZATION'],
        mergeIds: ['ORGANIZATION'],
    },
    clientOrganization: {
        ...getPageNames(['clientsOrganization', 'clientOrganization']),
        ...getPageBasics(
            'fa-briefcase-blank',
            'CLIENT_ORGANIZATION',
            {
                fields: getPageFields('CLIENT_ORGANIZATION'),
                features: features.mainWithPrioritiesFavorites,
            }
        ),
        templateRefs: ['CLIENT_ORGANIZATION'],
        mergeIds: ['ORGANIZATION'],
    },
    clientGeneric: {
        ...getPageNames(['clients', 'client']),
        ...getPageBasics(
            'fa-address-book',
            'CLIENT_GENERIC',
            {
                fields: getPageFields('CLIENT_GENERIC'),
                features: features.mainWithPrioritiesFavorites,
            }
        ),
        templateRefs: ['CLIENT_GENERIC'],
    },
    educationInstitution: {
        ...getPageNames(['educationInstitutions', 'educationInstitution', 'potentialSchools']),
        ...getPageBasics(
            'fa-school-circle-check',
            'EDUCATION_INSTITUTION',
            {
                fields: getPageFields('EDUCATION_INSTITUTION'),
                features: features.getFeatures(features.mainWithPrioritiesFavorites, ['TIMEKEEPER']),
                relationships: [
                    getRelationship('program', 'EDUCATION_PROGRAM', 'institution', 'ONE_TO_MANY'),
                ],
            }
        ),
        templateRefs: ['EDUCATION_INSTITUTION'],
    },
    publisher: {
        ...getPageNames(['publishers', 'publisher']),
        ...getPageBasics(
            'fa-book-circle-arrow-up',
            'PUBLISHER',
            {
                fields: getPageFields('PUBLISHER'),
                features: features.getFeatures(features.mainWithPrioritiesFavorites,
                    ['TIMEKEEPER', 'LINKS', 'EMAILS']),
                relationships: [
                    getRelationship('storiesToSend', 'STORY', 'potentialPublishers', 'MANY_TO_MANY'),
                    getRelationship('booksToSend', 'NON_FICTION_BOOK', 'potentialPublishers', 'MANY_TO_MANY'),
                ],
            }
        ),
        templateRefs: ['PUBLISHER'],
    },

    // Job positions
    jobPosition: {
        ...getPageNames(['positions', 'position']),
        ...getPageBasics(
            'fa-briefcase',
            'JOB_POSITION',
            {
                fields: getPageFields('JOB_POSITION'),
                markerGroups: ['APPLICATION_STATUS'],
                features: features.getFeatures(features.mainWithPrioritiesFavorites,
                    ['TIMEKEEPER', 'LINKS', 'EMAILS']),
            }
        ),
        includeInPages: true,
        templateRefs: ['JOB_POSITION'],
    },
    execJobPosition: {
        ...getPageNames(['positions', 'position']),
        ...getPageBasics(
            'fa-briefcase',
            'JOB_POSITION',
            {
                fields: getPageFields('EXEC_JOB_POSITION'),
                markerGroups: ['APPLICATION_STATUS'],
                features: features.getFeatures(features.mainWithPrioritiesFavorites,
                    ['TIMEKEEPER', 'LINKS', 'EMAILS']),
            }
        ),
        templateRefs: ['JOB_POSITION'],
    },
    openPosition: {
        ...getPageNames(['openPositions', 'openPosition']),
        ...getPageBasics(
            'fa-briefcase',
            'OPEN_POSITION',
            {
                fields: getPageFields('OPEN_POSITION'),
                markerGroups: ['HIRING_STATUS'],
                features: features.getFeatures(features.mainFeatures,
                    ['TIMEKEEPER', 'LINKS', 'EMAILS', 'PRIORITIES']),
            }
        ),
        templateRefs: ['OPEN_POSITION'],
    },

    // Courses
    course: {
        ...getPageNames(['courses', 'course']),
        ...getPageBasics(
            'fa-chalkboard',
            'COURSE',
            {
                fields: getPageFields('COURSE'),
                features: features.getFeatures(features.mainWithPrioritiesFavorites,
                    ['LINKS']),
            }
        ),
        templateRefs: ['COURSE'],
    },
    educationCourse: {
        ...getPageNames(['educationCourses', 'educationCourse', 'myCourses']),
        ...getPageBasics(
            'fa-chalkboard',
            'EDUCATION_COURSE',
            {
                fields: getPageFields('EDUCATION_COURSE'),
                features: features.getFeatures(features.mainWithPrioritiesFavorites,
                    ['LINKS']),
            }
        ),
        templateRefs: ['EDUCATION_COURSE'],
    },
    class: {
        ...getPageNames(['classes', 'class']),
        ...getPageBasics(
            'fa-bell-school',
            'CLASS',
            {
                fields: getPageFields('CLASS'),
                features: features.infoFeatures,
            }
        ),
        templateRefs: ['CLASS'],
    },
    educationProgram: {
        ...getPageNames(['programs', 'program', 'potentialPrograms']),
        ...getPageBasics(
            'fa-chalkboard',
            'EDUCATION_PROGRAM',
            {
                fields: getPageFields('EDUCATION_PROGRAM'),
                markerGroups: ['APPLICATION_STATUS'],
                features: features.getFeatures(features.mainWithPrioritiesFavorites,
                    ['TIMEKEEPER', 'LINKS', 'EMAILS']),
            }
        ),
        templateRefs: ['EDUCATION_PROGRAM'],
    },
    scholarship: {
        ...getPageNames(['scholarships', 'scholarship']),
        ...getPageBasics(
            'fa-money-check-dollar',
            'SCHOLARSHIP',
            {
                fields: getPageFields('SCHOLARSHIP'),
                markerGroups: ['APPLICATION_STATUS'],
                features: features.getFeatures(features.mainWithPrioritiesFavorites,
                    ['TIMEKEEPER', 'LINKS']),
                relationships: [
                    getRelationship('programs', 'EDUCATION_PROGRAM', 'scholarships', 'MANY_TO_MANY'),
                    getRelationship('institution', 'EDUCATION_INSTITUTION', 'scholarships', 'ONE_TO_MANY'),
                ],
            }
        ),
        includeInPages: true,
        templateRefs: ['SCHOLARSHIP'],
    },

    // Projects
    coursework: {
        ...getPageNames(['coursework', 'coursework']),
        ...getPageBasics(
            'fa-file-pen',
            'COURSEWORK',
            {
                fields: getPageFields('COURSEWORK'),
                markerGroups: ['COURSEWORK_STATUS'],
                features: features.getFeatures(features.mainWithPrioritiesFavorites,
                    ['TIMEKEEPER', 'LINKS']),
                relationships: [
                    getRelationship('course', 'EDUCATION_COURSE', 'coursework', 'ONE_TO_MANY'),
                ],
            }
        ),
        templateRefs: ['COURSEWORK'],
    },
    teachingAssignment: {
        ...getPageNames(['classAssignments', 'classAssignment']),
        ...getPageBasics(
            'fa-file-pen',
            'TEACHING_ASSIGNMENT',
            {
                fields: getPageFields('TEACHING_ASSIGNMENT'),
                features: features.getFeatures(features.mainWithPrioritiesFavorites,
                    ['LINKS']),
                relationships: [
                    getRelationship('classes', 'CLASS', 'assignments', 'MANY_TO_MANY'),
                ],
            }
        ),
        templateRefs: ['TEACHING_ASSIGNMENT'],
    },
    hobbiesProject: {
        ...getPageNames(['hobbiesProjects', 'hobbiesProject', 'myProjects']),
        ...getPageBasics(
            'fa-ruler-combined',
            'HOBBIES_PROJECT',
            {
                fields: getPageFields('HOBBIES_PROJECT'),
                markerGroups: ['PROJECT_STATUS'],
                features: features.allFeatures,
            }
        ),
        templateRefs: ['HOBBIES_PROJECT'],
    },
    homeImprovementProject: {
        ...getPageNames(['homeImprovementProjects', 'homeImprovementProject']),
        ...getPageBasics(
            'fa-house-day',
            'HOME_IMPROVEMENT_PROJECT',
            {
                fields: getPageFields('HOME_IMPROVEMENT_PROJECT'),
                markerGroups: ['PROJECT_STATUS'],
                features: features.allFeatures,
            }
        ),
        includeInPages: true,
        templateRefs: ['HOME_IMPROVEMENT_PROJECT'],
    },
    gardeningProject: {
        ...getPageNames(['gardeningProjects', 'gardeningProject']),
        ...getPageBasics(
            'fa-bag-seedling',
            'GARDENING_PROJECT',
            {
                fields: getPageFields('GARDENING_PROJECT'),
                markerGroups: ['PROJECT_STATUS'],
                features: features.infoFeatures,
            }
        ),
        templateRefs: ['GARDENING_PROJECT'],
    },
    hobbiesArt: {
        ...getPageNames(['artProjects', 'artProject']),
        ...getPageBasics(
            'fa-palette',
            'HOBBIES_ART',
            {
                fields: getPageFields('HOBBIES_ART'),
                markerGroups: ['PROJECT_STATUS'],
                features: features.infoFeatures,
            }
        ),
        includeInPages: true,
        templateRefs: ['HOBBIES_ART'],
    },
    photographyHobby: {
        ...getPageNames(['photographyProjects', 'photographyProject']),
        ...getPageBasics(
            'fa-camera',
            'PHOTOGRAPHY_HOBBY',
            {
                fields: getPageFields('PHOTOGRAPHY_HOBBY'),
                markerGroups: ['PROJECT_STATUS'],
                features: features.infoFeatures,
            }
        ),
        templateRefs: ['PHOTOGRAPHY_HOBBY'],
    },
    jewelryMaking: {
        ...getPageNames(['jewelryProjects', 'jewelryProject']),
        ...getPageBasics(
            'fa-ring',
            'JEWELRY_MAKING',
            {
                fields: getPageFields('JEWELRY_MAKING'),
                markerGroups: ['PROJECT_STATUS'],
                features: features.infoFeatures,
            }
        ),
        templateRefs: ['JEWELRY_MAKING'],
    },
    sewing: {
        ...getPageNames(['sewingProjects', 'sewingProject']),
        ...getPageBasics(
            'fa-reel',
            'SEWING',
            {
                fields: getPageFields('SEWING'),
                markerGroups: ['PROJECT_STATUS'],
                features: features.infoFeatures,
            }
        ),
        templateRefs: ['SEWING'],
    },
    woodworking: {
        ...getPageNames(['woodworkingProjects', 'woodworkingProject']),
        ...getPageBasics(
            'fa-axe',
            'WOODWORKING',
            {
                fields: getPageFields('WOODWORKING'),
                markerGroups: ['PROJECT_STATUS'],
                features: features.infoFeatures,
            }
        ),
        templateRefs: ['WOODWORKING'],
    },
    knitting: {
        ...getPageNames(['knittingProjects', 'knittingProject']),
        ...getPageBasics(
            'fa-scarf',
            'KNITTING',
            {
                fields: getPageFields('KNITTING'),
                markerGroups: ['PROJECT_STATUS'],
                features: features.infoFeatures,
            }
        ),
        templateRefs: ['KNITTING'],
    },

    hobbiesCollection: {
        ...getPageNames(['collectionItems', 'collectionItem', 'myCollection']),
        ...getPageBasics(
            'fa-album-collection',
            'HOBBIES_COLLECTION',
            {
                fields: getPageFields('HOBBIES_COLLECTION'),
                features: features.infoFeatures,
            }
        ),
        templateRefs: ['HOBBIES_COLLECTION'],
    },
    professionalProject: {
        ...getPageNames(['projects', 'project']),
        ...getPageBasics(
            'fa-bars-progress',
            'PROFESSIONAL_PROJECT',
            {
                fields: getPageFields('PROFESSIONAL_PROJECT'),
                markerGroups: ['PROJECT_STATUS'],
                features: features.allFeatures,
            }
        ),
        includeInPages: true,
        templateRefs: ['PROFESSIONAL_PROJECT'],
    },
    professionalProduct: {
        ...getPageNames(['products', 'product']),
        ...getPageBasics(
            'fa-circle-star',
            'PROFESSIONAL_PRODUCT',
            {
                fields: getPageFields('PROFESSIONAL_PRODUCT'),
                features: features.infoFeatures,
            }
        ),
        includeInPages: true,
        templateRefs: ['PROFESSIONAL_PRODUCT'],
    },
    socialMediaPost: {
        ...getPageNames(['socialMediaPosts', 'socialMediaPost', 'socialMediaContent']),
        ...getPageBasics(
            'fa-hashtag',
            'SOCIAL_MEDIA_POST',
            {
                fields: getPageFields('SOCIAL_MEDIA_POST'),
                markerGroups: ['PROJECT_STATUS'],
                features: features.allFeatures,
            }
        ),
        includeInPages: true,
        templateRefs: ['SOCIAL_MEDIA_POST'],
    },
    recipe: {
        ...getPageNames(['recipes', 'recipe']),
        ...getPageBasics(
            'fa-pot-food',
            'RECIPE',
            {
                fields: getPageFields('RECIPE'),
                features: features.infoFeatures,
            }
        ),
        includeInPages: true,
        templateRefs: ['RECIPE'],
    },
    story: {
        ...getPageNames(['stories', 'story', 'allMyStories']),
        ...getPageBasics(
            'fa-book-sparkles',
            'STORY',
            {
                fields: getPageFields('STORY'),
                features: features.infoFeatures,
                markerGroups: ['PROJECT_STATUS', 'GENRE_TAGS'],
                relationships: [
                    getRelationship('characters', 'STORY_CHARACTER', 'stories', 'MANY_TO_MANY'),
                    getRelationship('settings', 'STORY_SETTING', 'stories', 'MANY_TO_MANY'),
                ],
            }
        ),
        templateRefs: ['STORY'],
    },
    storySetting: {
        ...getPageNames(['storySettings', 'storySetting', 'allMySettings']),
        ...getPageBasics(
            'fa-map',
            'STORY_SETTING',
            {
                fields: getPageFields('STORY_SETTING'),
                features: features.infoFeatures,
                relationships: [
                    getRelationship('characters', 'STORY_CHARACTER', 'relatedSettings', 'MANY_TO_MANY'),
                ],
            }
        ),
        templateRefs: ['STORY_SETTING'],
    },
    storyCharacter: {
        ...getPageNames(['characters', 'character', 'allMyCharacters']),
        ...getPageBasics(
            'fa-pot-food',
            'STORY_CHARACTER',
            {
                fields: getPageFields('STORY_CHARACTER'),
                features: features.infoFeatures,
            }
        ),
        templateRefs: ['STORY_CHARACTER'],
    },
    nonFictionBook: {
        ...getPageNames(['nonFictionBooks', 'nonFictionBook', 'allMyNonFictionBooks']),
        ...getPageBasics(
            'fa-book-atlas',
            'NON_FICTION_BOOK',
            {
                fields: getPageFields('NON_FICTION_BOOK'),
                markerGroups: ['PROJECT_STATUS'],
                features: features.infoFeatures,
            }
        ),
        templateRefs: ['NON_FICTION_BOOK'],
    },
    hobbiesBook: {
        ...getPageNames(['books', 'book', 'myReadingList']),
        ...getPageBasics(
            'fa-book-sparkles',
            'HOBBIES_BOOK',
            {
                fields: getPageFields('HOBBIES_BOOK'),
                markerGroups: ['ACTION_STATUS', 'GENRE_TAGS', 'RATING_TAGS'],
                features: features.infoFeatures,
            }
        ),
        templateRefs: ['HOBBIES_BOOK'],
    },

    // Misc
    bucketList: {
        ...getPageNames(['bucketListItems', 'bucketListItem', 'myBucketList']),
        ...getPageBasics(
            'fa-face-laugh-beam',
            'BUCKET_LIST',
            {
                fields: getPageFields('BUCKET_LIST'),
                markerGroups: ['EVENT_THEME_TAGS'],
                features: features.infoFeatures,
            }
        ),
        templateRefs: ['BUCKET_LIST'],
    },
    gift: {
        ...getPageNames(['gifts', 'gift']),
        ...getPageBasics(
            'fa-gift',
            'GIFT',
            {
                fields: getPageFields('GIFT'),
                markerGroups: ['GIFT_TAGS', 'GIFT_STATUS'],
                features: features.infoFeatures,
            }
        ),
        includeInPages: true,
        templateRefs: ['GIFT'],
    },
    vehicle: {
        ...getPageNames(['vehicles', 'vehicle']),
        ...getPageBasics(
            'fa-car-side',
            'VEHICLE',
            {
                fields: getPageFields('VEHICLE'),
                features: features.infoFeatures,
            }
        ),
        includeInPages: true,
        templateRefs: ['VEHICLE'],
    },
    bill: {
        ...getPageNames(['expenses', 'expense']),
        ...getPageBasics(
            'fa-money-bill-transfer',
            'BILL',
            {
                fields: getPageFields('BILL'),
                features: features.infoFeatures,
            }
        ),
        includeInPages: true,
        templateRefs: ['BILL'],
    },
    subscription: {
        ...getPageNames(['subscriptions', 'subscription']),
        ...getPageBasics(
            'fa-credit-card',
            'SUBSCRIPTION',
            {
                fields: getPageFields('SUBSCRIPTION'),
                features: features.infoFeatures,
            }
        ),
        includeInPages: true,
        templateRefs: ['SUBSCRIPTION'],
    },

    // Starters
    starterItem: {
        ...getPageNames(['items', 'item']),
        ...getPageBasics(
            'fa-circle-half-stroke',
            'STARTER_ITEM',
            {
                fields: getPageFields('STARTER_ITEM'),
                features: features.allFeatures,
            }
        ),
        templateRefs: ['STARTER_ITEM'],
        notMatchable: true,
    },
};

// Specifics

const educationPersonSpecifics = {
    ...getPageNames(['educationContacts', 'educationContact', 'peopleAtSchool']),
    symbol: 'fa-person-chalkboard',
    relationships: [
        getRelationship('extracurricularContacts', 'EXTRACURRICULAR', 'people', 'MANY_TO_MANY'),
        getRelationship('courseContacts', 'EDUCATION_COURSE', 'people', 'MANY_TO_MANY'),
        getRelationship('courseworkContacts', 'COURSEWORK', 'people', 'MANY_TO_MANY'),
    ],
    includeInPages: false,
    markerGroups: ['EDUCATION_PEOPLE_TAGS'],
    templateRefs: ['EDUCATION_PERSON'],
};

const eventPersonSpecifics = {
    ...getPageNames(['eventPeople', 'eventPerson']),
    symbol: 'fa-person-rays',
    relationships: [
        getRelationship('parties', 'PARTY', 'people', 'MANY_TO_MANY'),
        getRelationship('events', 'EVENT', 'people', 'MANY_TO_MANY'),
    ],
    // TODO
    // markerGroups: ['EVENT_PEOPLE_TAGS'],
    includeInPages: false,
    templateRefs: ['EVENT_PERSON'],
};

const personPersonalSpecifics = {
    ...getPageNames(['personalContacts', 'personalContact']),
    symbol: 'fa-people-group',
    includeInPages: true,
    markerGroups: ['PERSON_PERSONAL_TAGS'],
    templateRefs: ['PERSON_PERSONAL'],
};

const networkContactSpecifics = {
    ...getPageNames(['networkContacts', 'networkContact', 'myNetwork']),
    symbol: 'fa-handshake',
    includeInPages: false,
    templateRefs: ['NETWORK_CONTACT'],
};

const coachingStudentSpecifics = {
    ...getPageNames(['coachingStudents', 'coachingStudent']),
    symbol: 'fa-medal',
    templateRefs: ['COACHING_STUDENT'],
};

const tutoringStudentSpecifics = {
    ...getPageNames(['tutoringStudents', 'tutoringStudent']),
    symbol: 'fa-book-open-reader',
    templateRefs: ['TUTORING_STUDENT'],
};

const networkOrganizationSpecifics = {
    ...getPageNames(['networkOrganizations', 'networkOrganization', 'organizationsInMyNetwork']),
    relationships: [
        getRelationship('employees', 'NETWORK_CONTACT', 'organizations', 'MANY_TO_MANY'),
    ],
    symbol: 'fa-building-flag',
    templateRefs: ['NETWORK_ORGANIZATION'],
};

const targetCompanySpecifics = {
    ...getPageNames(['targetCompanies', 'targetCompany']),
    relationships: [
        getRelationship('positions', 'JOB_POSITION', 'organization', 'ONE_TO_MANY'),
        getRelationship('employees', 'CAREER_CONTACT', 'organizations', 'MANY_TO_MANY'),
    ],
    symbol: 'fa-building-circle-check',
    templateRefs: ['TARGET_COMPANY'],
};

const eventSupplierSpecifics = {
    ...getPageNames(['eventSuppliers', 'eventSupplier', 'eventSuppliersAndServices']),
    relationships: [
        getRelationship('events', 'EVENT', 'suppliers', 'MANY_TO_MANY'),
        getRelationship('parties', 'PARTY', 'suppliers', 'MANY_TO_MANY'),
    ],
    markerGroups: ['SERVICE_TAGS'],
    symbol: 'fa-icons',
    templateRefs: ['EVENT_SUPPLIER'],
};

const weddingServiceSpecifics = {
    ...getPageNames(['vendorsAndServices', 'vendorOrService']),
    relationships: [
        getRelationship('wedding', 'WEDDING', 'vendorsAndServices', 'ONE_TO_MANY'),
    ],
    markerGroups: ['SERVICE_TAGS'],
    symbol: 'fa-icons',
    templateRefs: ['WEDDING_SERVICE'],
};

const singleActivitySpecifics = {
    ...getPageNames({ pageName: 'myActivity' }),
    symbol: 'fa-person-hiking',
    pageType: 'ENTITY',
    includeInPages: true,
    altPageName: 'activity',
    templateRefs: ['ACTIVITY'],
};

const singleProjectSpecifics = {
    ...getPageNames({ pageName: 'project' }),
    symbol: 'fa-rectangle-vertical-history',
    pageType: 'ENTITY',
    includeInPages: true,
    templateRefs: ['PROFESSIONAL_PROJECT'],
};

const singleProductSpecifics = {
    ...getPageNames({ pageName: 'product' }),
    symbol: 'fa-certificate',
    pageType: 'ENTITY',
    includeInPages: true,
    templateRefs: ['PROFESSIONAL_PRODUCT'],
};

const furtherEducationCourseSpecifics = {
    ...getPageNames(['furtherEducationCourses', 'furtherEducationCourse']),
    symbol: 'fa-laptop-file',
    templateRefs: ['FURTHER_EDUCATION_COURSE'],
};

const partySpecifics = {
    ...getPageNames(['parties', 'party', 'myParties']),
    symbol: 'fa-party-horn',
    altPageName: 'parties',
    fields: getPageFields('PARTY'),
    templateRefs: ['PARTY'],
};

const eventVenueSpecifics = {
    ...getPageNames(['eventVenues', 'eventVenue']),
    relationships: [
        getRelationship('events', 'EVENT', 'venues', 'MANY_TO_MANY'),
        getRelationship('parties', 'PARTY', 'venues', 'MANY_TO_MANY'),
    ],
    symbol: 'fa-tree-city',
    markerGroups: ['SERVICE_TAGS'],
    templateRefs: ['EVENT_VENUE'],
};

const weddingVenueSpecifics = {
    ...getPageNames(['weddingVenues', 'weddingVenue']),
    relationships: [
        getRelationship('wedding', 'WEDDING', 'potentialVenues', 'ONE_TO_MANY'),
    ],
    symbol: 'fa-place-of-worship',
    markerGroups: ['SERVICE_TAGS'],
    templateRefs: ['WEDDING_VENUE'],
};

const specificPages = {
    educationPerson: getPageWithSpecifics(
        basePages.person,
        educationPersonSpecifics,
        'EDUCATION_PERSON'),

    personPersonal: getPageWithSpecifics(
        basePages.person,
        personPersonalSpecifics,
        'PERSON_PERSONAL'),

    eventPerson: getPageWithSpecifics(
        basePages.person,
        eventPersonSpecifics,
        'EVENT_PERSON'),

    networkContact: getPageWithSpecifics(
        basePages.careerContact,
        networkContactSpecifics,
        'NETWORK_CONTACT'),

    coachingStudent: getPageWithSpecifics(
        basePages.student,
        coachingStudentSpecifics,
        'COACHING_STUDENT'),

    tutoringStudent: getPageWithSpecifics(
        basePages.student,
        tutoringStudentSpecifics,
        'TUTORING_STUDENT'),

    targetCompany: getPageWithSpecifics(
        basePages.organization,
        targetCompanySpecifics,
        'TARGET_COMPANY'),

    networkOrganization: getPageWithSpecifics(
        basePages.organization,
        networkOrganizationSpecifics,
        'NETWORK_ORGANIZATION'),

    eventSupplier: getPageWithSpecifics(
        basePages.organization,
        eventSupplierSpecifics,
        'EVENT_SUPPLIER'),

    weddingService: getPageWithSpecifics(
        basePages.organization,
        weddingServiceSpecifics,
        'WEDDING_SERVICE'),

    singleProject: getPageWithSpecifics(
        basePages.professionalProject,
        singleProjectSpecifics,
        'SINGLE_PROJECT'),
    singleProduct: getPageWithSpecifics(
        basePages.professionalProduct,
        singleProductSpecifics,
        'SINGLE_PRODUCT'),

    singleActivity: getPageWithSpecifics(
        basePages.activity,
        singleActivitySpecifics,
        'SINGLE_ACTIVITY'),

    furtherEducationCourse: getPageWithSpecifics(
        basePages.course,
        furtherEducationCourseSpecifics,
        'FURTHER_EDUCATION_COURSE'),

    party: getPageWithSpecifics(
        basePages.event,
        partySpecifics,
        'PARTY'),

    eventVenues: getPageWithSpecifics(
        basePages.venue,
        eventVenueSpecifics,
        'EVENT_VENUE'),

    weddingVenue: getPageWithSpecifics(
        basePages.venue,
        weddingVenueSpecifics,
        'WEDDING_VENUE'),
};

export const allPages = {
    ...featurePages,
    ...specificFeaturePages,
    ...basePages,
    ...specificPages,
};

// For the individual page picker
export const filteredAvailablePages = _(allPages).filter((page) => {
    return page.includeInPages;
}).value();

export function availablePages() {
    const pages = [];
    filteredAvailablePages.forEach((page) => {
        const newPage = page;
        if (page.singleOrMultiple) {
            const singlePage = {
                ...page,
                id: `${page.id}_SINGLE`,
                pageType: 'ENTITY',
                pageName: page.singularName,
            };
            pages.push(singlePage);
        }
        if (page.altPageName) {
            newPage.pageName = getPageNames({ pageName: page.altPageName }).pageName;
        }
        pages.push(newPage);
    });
    return pages;
}
