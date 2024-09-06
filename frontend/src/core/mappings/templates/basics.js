// Common patterns/groupings of fields
import { warn } from 'vue';

import * as fields from '@/core/mappings/templates/fields.js';

// General
function defaultBasic(nameKey = 'NAME') {
    return [
        {
            ...fields.SYSTEM_NAME,
            nameKey,
        },
        fields.DESCRIPTION,
    ];
}

function defaultMain(nameKey = 'NAME') {
    return [
        {
            ...fields.SYSTEM_NAME,
            nameKey,
        },
        fields.DESCRIPTION,
        fields.IMAGE,
    ];
}

const fullPreferredName = [
    fields.SYSTEM_NAME_FULL,
    fields.PREFERRED_NAME,
];

const defaultPerson = [
    ...fullPreferredName,
    fields.IMAGE,
];

// Contact info
const contactInfoDefaults = [
    fields.ADDRESSES,
    fields.PHONES,
    fields.EMAILS,
    fields.LINKS,
];

// Details
const organizationBasics = [
    fields.ADDRESSES_FREE_LABEL,
    fields.EMAILS_FREE_LABEL,
    fields.PHONES_FREE_LABEL,
    fields.LINKS,
    fields.WORLDWIDE_LOCATIONS,
];

const organizationDetails = organizationBasics.concat([
    fields.LOGO,
    fields.ORGANIZATION_TYPE,
]);

const educationInstitutionDetails = organizationBasics.concat([
    fields.EXTRACURRICULARS,
    fields.CAMPUS_LIFE,
]);

const publisherDetails = organizationBasics.concat([
    fields.MANUSCRIPT_REQUIREMENTS,
]);

const careerContactDetails = [
    ...contactInfoDefaults,
    fields.BASIC_POSITIONS,
];

const casualContactDetails = [
    ...contactInfoDefaults,
    fields.BIRTHDAY,
];

const applicantDetails = [
    ...contactInfoDefaults,
    fields.NOTICE,
    fields.START_DATE,
    fields.SALARY_EXPECTATION,
];

const weddingGuestDetails = [
    ...contactInfoDefaults,
    fields.IS_WEDDING_GUEST,
];

const studentDetails = casualContactDetails.concat([
    fields.GOALS,
]);

const classStudentDetails = studentDetails.concat([
    fields.GRADE,
]);

const extracurricularDetails = [
    fields.ESTIMATED_COST,
    fields.SCHEDULE,
    fields.VENUES,
    fields.CLUB_NAME,
];

const jobPositionDetails = [
    fields.ROLE,
    fields.INDUSTRY,
    fields.WORLDWIDE_LOCATIONS,
    fields.SALARY_RANGE,
    fields.REQUIREMENTS,
    fields.BENEFITS,
    fields.ATTENDANCE_TYPE,
    fields.COMMUTE_DURATION,
];

const execJobPositionDetails = [
    ...jobPositionDetails,
    fields.BONUS,
];

const openPositionDetails = [
    fields.SALARY_RANGE,
    fields.REQUIREMENTS,
    fields.ATTENDANCE_TYPE,
    fields.POSITION_TYPE,
];

const courseDetails = [
    fields.CREDITS,
    fields.ADDRESS,
    fields.DURATION,
    fields.COST,
    fields.ATTENDANCE_TYPE,
    fields.SCHEDULE,
];

const educationCourseDetails = [
    fields.CREDITS,
    fields.ADDRESS,
    fields.COST,
    fields.ATTENDANCE_TYPE,
    fields.SCHEDULE,
];

const programDetails = [
    fields.DURATION,
    fields.COST,
    fields.ATTENDANCE_TYPE,
    fields.SCHEDULE,
    fields.REQUIREMENTS,
    fields.ESTIMATED_COST,
];

const destinationDetails = [
    fields.TRIP_DURATION_RANGE,
    fields.WHAT_I_LIKE,
    fields.ESTIMATED_COST,
];

const activityDetails = [
    fields.DURATION,
    fields.ESTIMATED_COST,
    fields.WORLDWIDE_LOCATIONS,
    fields.ADDRESS,
];

const childrensActivityDetails = [
    fields.ESTIMATED_COST,
    fields.ADDRESS,
];

const classDetails = [
    fields.GOALS,
];

const scholarshipDetails = [
    fields.VALUE,
    fields.REQUIREMENTS,
];

const courseworkDetails = [
    fields.GRADE,
    fields.PLAN,
];

const hobbyProjectDetails = [
    fields.DIFFICULTY,
    fields.DURATION,
    fields.EQUIPMENT,
];

const professionalProductDetails = [
];

const homeImprovementDetails = [
    fields.DIFFICULTY,
    fields.DURATION,
    fields.EQUIPMENT,
];

const photographyHobbyDetails = [
    fields.LOCATION_LINE,
];

const professionalProjectDetails = [
    fields.DIFFICULTY,
    fields.DURATION,
];

const recipeDetails = [
    fields.CUISINE,
    fields.DURATION,
    fields.DIFFICULTY,
    fields.SOURCE,
    fields.IVE_DONE_IT,
    fields.PREP_AHEAD,
];

const accommodationDetails = [
    fields.ESTIMATED_COST,
    fields.WORLDWIDE_LOCATIONS,
    fields.ADDRESS,
    fields.AMENITIES,
    fields.WHAT_I_LIKE,
];

const teachingAssignmentDetails = [
    fields.PLAN,
    fields.GOALS,
];

const bucketListDetails = [
    fields.WORLDWIDE_LOCATIONS,
    fields.ESTIMATED_COST,
    fields.COMPLETION_DATE,
    fields.TIMEFRAME,
    fields.WHAT_I_LIKE,
];

const giftDetails = [
    fields.OCCASION,
    fields.ESTIMATED_COST,
    fields.COST,
    fields.LINKS,
];

const expenseDetails = [
    fields.SERVICE_PROVIDER,
    fields.COST,
    fields.PAYMENT_METHOD,
    fields.PAYMENT_INTERVAL,
];

const subscriptionDetails = [
    fields.COST,
    fields.PROVIDER,
    fields.PAYMENT_INTERVAL,
    fields.PAYMENT_METHOD,
    fields.IS_AUTO_RENEW,
];

const vehicleDetails = [
    fields.MAKE,
    fields.MODEL,
    fields.LICENSE_PLATE,
    fields.YEAR,
    fields.COLOR,
    fields.VIN,
    fields.INSURANCE_INFO,
    fields.DATE_PURCHASED,
];

const petDetails = [
    fields.BIRTHDAY,
    fields.CHIP_ID,
    fields.PET_NEEDS,
    fields.DIET_INFO,
    fields.MEDICAL_INFO,
    fields.INSURANCE_INFO,
];

export const fieldGroupings = {
    // People
    PERSON_FIELDS: defaultPerson.concat(casualContactDetails),
    CAREER_CONTACT_FIELDS: defaultPerson.concat(careerContactDetails),
    STUDENT_FIELDS: fullPreferredName.concat(studentDetails),
    CLASS_STUDENT_FIELDS: fullPreferredName.concat(classStudentDetails),
    WEDDING_GUEST_SUBSET_FIELDS: defaultPerson.concat(weddingGuestDetails),
    APPLICANT_FIELDS: fullPreferredName.concat(applicantDetails),
    CLIENT_PERSON_FIELDS: defaultPerson.concat(casualContactDetails),
    CHILD_FIELDS: defaultPerson,

    // Organizations
    ORGANIZATION_FIELDS: defaultBasic('ORGANIZATION').concat(organizationDetails),
    EDUCATION_INSTITUTION_FIELDS: defaultMain('SCHOOL').concat(educationInstitutionDetails),
    PUBLISHER_FIELDS: defaultBasic('PUBLISHER').concat(publisherDetails),
    SUPPLIER_FIELDS: defaultMain('SUPPLIER'),
    CLIENT_ORGANIZATION_FIELDS: defaultBasic('CLIENT').concat(organizationDetails),
    CLIENT_GENERIC_FIELDS: defaultBasic('CLIENT'),

    // Travel and activities
    DESTINATION_FIELDS: defaultMain('DESTINATION').concat(destinationDetails),
    EXTRACURRICULAR_FIELDS: defaultMain('EXTRACURRICULAR').concat(extracurricularDetails),
    ACTIVITY_FIELDS: defaultMain('ACTIVITY').concat(activityDetails),
    CHILDRENS_ACTIVITY_FIELDS: defaultMain('ACTIVITY').concat(childrensActivityDetails),
    TRAVEL_ACTIVITY_FIELDS: defaultMain('ACTIVITY').concat(activityDetails),
    EVENT_FIELDS: defaultMain('EVENT_NAME'),
    PARTY_FIELDS: defaultMain('PARTY_NAME'),
    WEDDING_FIELDS: defaultMain(),
    // Settings
    ACCOMMODATION_FIELDS: defaultMain('ACCOMODATION').concat(accommodationDetails),
    VENUE_FIELDS: defaultMain('VENUE'),
    // Job positions
    JOB_POSITION_FIELDS: defaultBasic('POSITION').concat(jobPositionDetails),
    EXEC_JOB_POSITION_FIELDS: defaultBasic('POSITION').concat(execJobPositionDetails),
    OPEN_POSITION_FIELDS: defaultBasic('POSITION').concat(openPositionDetails),
    // Courses
    COURSE_FIELDS: defaultBasic('COURSE').concat(courseDetails),
    EDUCATION_COURSE_FIELDS: defaultBasic('COURSE').concat(educationCourseDetails),
    EDUCATION_PROGRAM_FIELDS: defaultBasic('PROGRAM').concat(programDetails),
    SCHOLARSHIP_FIELDS: defaultBasic('SCHOLARSHIP_NAME').concat(scholarshipDetails),
    CLASS_FIELDS: defaultBasic('CLASS').concat(classDetails),
    // Projects
    COURSEWORK_FIELDS: defaultBasic('ASSIGNMENT').concat(courseworkDetails),
    TEACHING_ASSIGNMENT_FIELDS: defaultBasic('ASSIGNMENT').concat(teachingAssignmentDetails),
    HOBBIES_PROJECT_FIELDS: defaultMain('PROJECT').concat(hobbyProjectDetails),
    HOME_IMPROVEMENT_PROJECT_FIELDS: defaultMain('HOME_PROJECT').concat(homeImprovementDetails),
    GARDENING_PROJECT_FIELDS: defaultMain('GARDENING_PROJECT').concat(hobbyProjectDetails),
    SEWING_FIELDS: defaultMain('SEWING_PROJECT').concat(hobbyProjectDetails),
    KNITTING_FIELDS: defaultMain('KNITTING_PROJECT').concat(hobbyProjectDetails),
    WOODWORKING_FIELDS: defaultMain('WOODWORKING_PROJECT').concat(hobbyProjectDetails),
    HOBBIES_ART_FIELDS: defaultMain('ART_PROJECT').concat(hobbyProjectDetails),
    JEWELRY_MAKING_FIELDS: defaultMain('JEWELRY_PROJECT').concat(hobbyProjectDetails),
    PHOTOGRAPHY_HOBBY_FIELDS: defaultMain('PHOTOGRAPHY_PROJECT').concat(photographyHobbyDetails),
    HOBBY_ART_FIELDS: defaultMain('ART_PROJECT').concat(hobbyProjectDetails),
    HOBBIES_COLLECTION_FIELDS: defaultMain('ITEM'),
    PROFESSIONAL_PROJECT_FIELDS: defaultBasic('PROJECT').concat(professionalProjectDetails),
    PROFESSIONAL_PRODUCT_FIELDS: defaultBasic('PRODUCT').concat(professionalProductDetails),
    SOCIAL_MEDIA_POST_FIELDS: defaultBasic('CONTENT_TITLE'),
    RECIPE_FIELDS: defaultMain('RECIPE').concat(recipeDetails),
    STORY_FIELDS: defaultBasic('STORY_TITLE'),
    STORY_CHARACTER_FIELDS: defaultMain(),
    STORY_SETTING_FIELDS: defaultMain('SETTING'),
    NON_FICTION_BOOK_FIELDS: defaultBasic('BOOK_TITLE'),
    HOBBIES_BOOK_FIELDS: defaultMain('BOOK_TITLE'),
    // Misc
    BUCKET_LIST_FIELDS: defaultMain('ACTIVITY').concat(bucketListDetails),
    GIFT_FIELDS: defaultMain('GIFT').concat(giftDetails),
    VEHICLE_FIELDS: defaultMain('VEHICLE').concat(vehicleDetails),
    PET_FIELDS: defaultMain('PET').concat(petDetails),
    BILL_FIELDS: defaultMain('EXPENSE').concat(expenseDetails),
    SUBSCRIPTION_FIELDS: defaultMain('SUBSCRIPTION').concat(subscriptionDetails),
    // Starters
    STARTER_ITEM_FIELDS: defaultMain(),

};

export function getPageFields(pageId, fieldsId) {
    const defaultFieldsPointer = `${pageId}_FIELDS`;
    const specificFieldsArr = fieldGroupings[defaultFieldsPointer] || fieldGroupings[fieldsId];
    if (!specificFieldsArr) {
        warn(`The page with id "${pageId}" is missing specific fields and relying on the default fields set`);
    }
    const hasUndefinedFields = specificFieldsArr && specificFieldsArr.some((field) => {
        return !field;
    });
    if (hasUndefinedFields) {
        const ids = specificFieldsArr.map((field) => {
            return field?.id || '***Missing this field***';
        });
        warn(`The page with id "${pageId}" has at least one undefined field. Please define in fields.js`);
        warn(ids);
    }
    return specificFieldsArr || defaultMain();
}
