import { extraList } from '@/core/display/accentColors.js';

import { $t } from '@/i18n.js';

export const markers = {
    TRIP_TYPE_TAGS: {
        id: 'TRIP_TYPE_TAGS',
        type: 'TAG',
        markers: [
            'beach',
            'city',
            'roadTrip',
            'active',
            'history',
            'adventure',
            'nature',
            'relaxation',
        ],
    },
    GENRE_TAGS: {
        id: 'GENRE_TAGS',
        type: 'TAG',
        markers: [
            'fantasy',
            'romance',
            'sci-fi',
            'classic',
            'horror',
            'comedy',
            'thriller',
            'action',
            'drama',
        ],
    },
    EVENT_THEME_TAGS: {
        id: 'EVENT_THEME',
        type: 'TAG',
        markers: [
            'adventure',
            'career',
            'personalDevelopment',
            'travel',
            'hobbies',
            'friendsAndFamily',
        ],
    },
    SERVICE_TAGS: {
        id: 'SERVICE_TAGS',
        type: 'TAG',
        markers: [
            'potential',
            'hired',
            'doNotHire',
            'greatImpression',
            'doubts',
            'withinBudget',
            'outsideBudget',
        ],
    },
    RATING_TAGS: {
        id: 'RATING_TAGS',
        type: 'TAG',
        markers: [
            'great',
            'good',
            'bad',
            'loveThis',
            'unsure',
            'lastResort',
            'notForMe',
        ],
    },
    GIFT_TAGS: {
        id: 'GIFT_TAGS',
        type: 'TAG',
        markers: [
            'nothingToSeeHere',
            'future',
            'now',
            'need',
            'want',
            'recurring',
            'needToThinkAboutIt',
            'hardToGet',
            'handmade',
            'online',
            'local',
        ],
    },
    APPLICATION_STATUS: {
        id: 'APPLICATION_STATUS',
        type: 'STATUS',
        markers: [
            'identified',
            'inProgress',
            'applying',
            'waitingForReply',
            'interview',
            'closed',
            'offerMade',
            'accepted',
            'notApplying',
        ],
    },
    CAREER_CONTACT_DESCRIPTOR_TAGS: {
        id: 'CAREER_CONTACT_DESCRIPTOR_TAGS',
        type: 'TAG',
        markers: [
            'acquaintance',
            'friend',
            'mentor',
            'goodSource',
            'colleague',
            'manager',
            'opportunity',
            'referee',
            'headhunter',
        ],
    },
    CONTACTED_PIPELINE: {
        id: 'CONTACTED_PIPELINE',
        type: 'PIPELINE',
        markers: [
            'awaitingReply',
            'contacted',
        ],
    },
    PROJECT_STATUS: {
        id: 'PROJECT_STATUS',
        type: 'STATUS',
        markers: [
            'started',
            'planning',
            'inProgress',
            'completed',
            'next',
        ],
    },
    ACTION_STATUS: {
        type: 'STATUS',
        markers: [
            'waiting',
            'next',
            'started',
            'inProgress',
            'completed',
        ],
    },
    GIFT_STATUS: {
        type: 'STATUS',
        markers: [
            'wait',
            'purchased',
            'ordered',
            'open',
            'delivered',
        ],
    },
    COURSEWORK_STATUS: {
        id: 'COURSEWORK_STATUS',
        type: 'STATUS',
        markers: [
            'started',
            'planning',
            'inProgress',
            'studying',
            'awaitingGrade',
            'done',
        ],
    },
    EDUCATION_PEOPLE_TAGS: {
        id: 'EDUCATION_PEOPLE_TAGS',
        type: 'TAG',
        markers: [
            'colleague',
            'professor',
            'instructor',
            'staff',
            'TA',
            'mentor',
            'friend',
        ],
    },
    PERSON_PERSONAL_TAGS: {
        id: 'PERSON_PERSONAL_TAGS',
        type: 'TAG',
        markers: [
            'friend',
            'family',
            'colleague',
            'medical',
            'acquaintance',
            'tradesperson',
            'community',
            'club',
            'instructor',
            'other',
        ],
    },
    HIRING_STATUS: {
        id: 'HIRING_STATUS',
        type: 'STATUS',
        markers: [
            'hired',
            'interviewing',
            'waitingToStart',
        ],
    },
    APPLICANT_TAGS: {
        id: 'APPLICANT_TAGS',
        type: 'TAG',
        markers: [
            'doNotHire',
            'potential',
            'promising',
            'goodFit',
        ],
    },
    APPLICANT_PIPELINE: {
        id: 'APPLICANT_PIPELINE',
        type: 'PIPELINE',
        markers: [
            'initialInterview',
            'assessment',
            'finalInterview',
            'awaitingContract',
            'notHired',
            'hired',
        ],
    },
};

function getRandomColor() {
    const set = ['bright', 'regular'];
    const setLength = set.length;
    const setVal = _.random(0, setLength - 1);
    const brightOrRegular = set[setVal];
    const colorArr = extraList.light[brightOrRegular];
    const lengthOfHues = colorArr.length;
    const hue = _.random(0, lengthOfHues - 1);
    const lengthOfShades = colorArr[hue].length;
    const shade = _.random(3, lengthOfShades - 1);
    return colorArr[hue][shade].val;
}

export function getMarkerGroup(markerId) {
    const markerObj = markers[markerId];

    if (markerObj) {
        const camelName = _.camelCase(markerId);
        return {
            name: $t(`labels.${camelName}`),
            id: markerId,
            type: markerObj.type,
            templateRefs: [markerId],
            markers: markerObj.markers.map((marker) => {
                return {
                    id: marker,
                    color: getRandomColor(),
                    name: $t(`tags.${marker}`),
                };
            }),
        };
    }
    return {};
}
