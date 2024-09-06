export const sectorMap = () => [
    {
        id: 'business',
        subSectors: [
            {
                id: 'businessConsulting',
            },
            {
                id: 'businessManagement',
            },
            {
                id: 'businessAccountancy',
            },
            {
                id: 'businessBanking',
            },
            {
                id: 'businessFinanceManagement',
            },
            {
                id: 'businessInsurance',
            },
            {
                id: 'businessFinancePlanning',
            },
            {
                id: 'businessAuditing',
            },
            {
                id: 'businessFinanceTax',
            },
            {
                id: 'businessActuarialServices',
            },
        ],
    },
    {
        id: 'social',
        subSectors: [
            {
                id: 'socialReligion',
            },
            {
                id: 'socialCommunity',
            },
            {
                id: 'socialCoaching',
            },
            {
                id: 'socialAccommodation',
            },
            {
                id: 'socialSupport',
            },
            {
                id: 'socialCharity',
            },
        ],
    },
    {
        id: 'engineering',
        subSectors: [
            {
                id: 'engineeringEngineering',
            },
            {
                id: 'engineeringManufacturing',
            },
            {
                id: 'engineeringArchitecture',
            },
        ],
    },
    {
        id: 'environment',
        subSectors: [
            {
                id: 'environmentVet',

            },
            {
                id: 'environmentFarming',
            },
            {
                id: 'environmentFisheries',
            },
            {
                id: 'environmentHorticulture',
            },
            {
                id: 'environmentGame',
            },
            {
                id: 'environmentForestry',
            },
            {
                id: 'environmentMining',

            },
        ],
    },
    {
        id: 'healthcare',
        subSectors: [
            {
                id: 'healthcareServices',
            },
            {
                id: 'healthcarePromotion',
            },
            {
                id: 'healthcareAdmin',
            },
        ],
    },
    {
        id: 'arts',
        subSectors: [
            {
                id: 'artsCommission',
            },
            {
                id: 'artsGraphicDesign',

            },
            {
                id: 'artsInteriorDesign',

            },
            {
                id: 'artsFashionDesign',

            },
            {
                id: 'artsPerforming',
            },
            {
                id: 'artsPhotography',
            },
            {
                id: 'artsPerformanceCenter',
            },
            {
                id: 'artsProductionCompany',
            },
            {
                id: 'artsWriting',
            },
            {
                id: 'artsPublishing',
            },
            {
                id: 'artsTranslation',
            },
            {
                id: 'artsMagazine',
            },
            {
                id: 'artsNews',
            },
            {
                id: 'artsBlog',
            },
            {
                id: 'artsTalentAgency',
            },
            {
                id: 'artsStation',
            },
            {
                id: 'artsSocialMedia',
            },
            {
                id: 'artsFilmmaking',

            },
        ],
    },
    {
        id: 'training',
        subSectors: [
            {
                id: 'trainingConference',
            },
            {
                id: 'trainingTutoring',
            },
            {
                id: 'trainingTeaching',
            },
            {
                id: 'trainingSeminars',
            },
            {
                id: 'trainingNursery',
            },
        ],
    },
    {
        id: 'transport',
        subSectors: [
            {
                id: 'transportPlanning',
            },
            {
                id: 'transportSupply',
            },
            {
                id: 'transportLogistics',
            },
            {
                id: 'transportShipping',
            },
        ],
    },
    {
        id: 'science',
        subSectors: [
            {
                id: 'scienceResearch',
            },
            {
                id: 'scienceLab',
            },
            {
                id: 'sciencePharmacy',
            },
            {
                id: 'sciencePharmaceuticals',
            },
        ],
    },
    {
        id: 'leisure',
        subSectors: [
            {
                id: 'leisureFood',
            },
            {
                id: 'leisureAccommodation',
            },
            {
                id: 'leisurePlanning',
            },
            {
                id: 'leisureManagement',
            },
            {
                id: 'leisureCatering',
            },
            {
                id: 'leisureTours',
            },
            {
                id: 'leisureTravelAgency',
            },
            {
                id: 'leisureCar',
            },
            {
                id: 'leisureExperiences',
            },
            {
                id: 'leisurePassengerServices',
            },
            {
                id: 'leisureCoaching',
            },
        ],
    },
    {
        id: 'technology',
        subSectors: [
            {
                id: 'technologyGames',
            },
            {
                id: 'technologyAnalysis',
            },
            {
                id: 'technologyDev',
            },
            {
                id: 'technologySaas',
            },
            {
                id: 'technologyConsulting',
            },
            {
                id: 'technologySupport',
            },
        ],
    },
    {
        id: 'marketing',
        subSectors: [
            {
                id: 'marketingMarketing',

            },
            {
                id: 'marketingAdvertising',

            },
            {
                id: 'marketingPr',

            },
            {
                id: 'marketingFundraising',

            },
        ],
    },

    // Excluded as it does not fit with Hylark's first release
    {
        id: 'law',
        excluded: true,
        subSectors: [
            {
                id: 'lawMediation',
            },
            {
                id: 'lawConveyance',
            },
            {
                id: 'lawServices',
            },
        ],
    },

    {
        id: 'security',
        subSectors: [
            {
                id: 'securitySecurity',
            },
            {
                id: 'securityPrivate',
            },
            {
                id: 'securityEmergency',

            },
            // {
            //     id: 'securityParole',
            //
            // },
        ],
    },

    {
        id: 'property',
        subSectors: [
            {
                id: 'propertyConstruction',
            },
            {
                id: 'propertyRealEstate',
            },
            {
                id: 'propertyManagement',
            },
            {
                id: 'propertySurveying',
            },
            {
                id: 'propertyValuation',
            },
            {
                id: 'propertyConservation',
            },
        ],
    },
    {
        id: 'recruitment',
        subSectors: [
            {
                id: 'recruitmentHr',
            },
            {
                id: 'recruitmentRecruitment',
            },
            {
                id: 'recruitmentCoach',
            },
        ],
    },
    {
        id: 'retail',
        subSectors: [
            {
                id: 'retailWarehouse',
            },
            {
                id: 'retailStore',
            },
            {
                id: 'retailPersonal',
            },
        ],
    },
    {
        id: 'sales',
        subSectors: [
            {
                id: 'salesSales',
            },
            {
                id: 'salesCustomer',
            },
            {
                id: 'salesDevelopment',
            },
        ],
    },
];

export default { sectorMap };
