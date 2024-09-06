import { warn } from 'vue';
import { $t } from '@/i18n.js';

import { getDefaults } from '@/core/mappings/templates/defaultData.js';
import { getViews } from '@/core/mappings/templates/views.js';
import { getMarkerGroup } from '@/core/mappings/templates/markers.js';
import { getCategory } from '@/core/mappings/templates/categories.js';
import { getLists } from '@/core/mappings/templates/lists.js';
import { pagesList } from '@/core/mappings/templates/bundles.js';
import { allPages } from '@/core/mappings/templates/pages.js';
import { getSubset } from '@/core/mappings/templates/entitySubsets.js';

import uses from '@/core/mappings/templates/uses.js';

const entityTypes = ['ENTITIES', 'ENTITY'];

function getDefaultView() {
    const views = ['LINE', 'TILE', 'SPREADSHEET'];
    const random = _.random(0, 2);
    return views[random];
}

// function getCombinedFeatures(spaceUses) {
//     const pages = _.flatMap(spaceUses, 'pages');
//     const pageFeatures = _.flatMap(pages, 'features');

//     return _.uniqBy(pageFeatures, 'val');
// }

function getAllLists(pages) {
    const flattened = _(pages).flatMap((page) => {
        const lists = page.lists;
        if (!lists) {
            return null;
        }
        return _(lists).map((list) => {
            return {
                listKey: page.pageType,
                id: list,
                mergeListsIds: page.mergeListsIds,
            };
        }).uniqBy('id').value();
    }).compact().value();
    if (!flattened?.length) {
        return null;
    }
    return flattened;
}

export function getCombinedLists(pages) {
    const allLists = getAllLists(pages);
    if (allLists) {
        const grouped = _.groupBy(allLists, 'listKey');
        const formatted = {};
        _.forEach(grouped, (lists, key) => {
            formatted[_.camelCase(key)] = getLists(lists);
        });
        return formatted;
    }
    return null;
}

function getPageSubsetInfo(subsetPages, page) {
    const subsetChildren = subsetPages.filter((subsetPage) => {
        return subsetPage.subset.mainId === page.id;
    });
    const childrenDefaults = _.map(subsetChildren, 'specificDefaults');

    const specificDefaults = {};
    childrenDefaults.forEach((child) => {
        Object.keys(child).forEach((key) => {
            if (!specificDefaults[key]) {
                specificDefaults[key] = {};
            }
            _.keys(child[key]).forEach((valKey) => {
                const path = specificDefaults[key][valKey];
                if (path) {
                    specificDefaults[key][valKey] = _(path).concat(child[key][valKey]).uniq().value();
                } else {
                    specificDefaults[key][valKey] = child[key][valKey];
                }
            });
        });
    });
    return specificDefaults;
}

function getEntityDefaultData(page, personTypePages, itemTypePages, index, otherPages) {
    const isSubset = page.subset;
    let subsetParentExamples = null;
    let subsetParentViews = null;
    if (isSubset) {
        const parent = _.find(otherPages, { id: page.subset.mainId });
        subsetParentExamples = parent.examples;
        subsetParentViews = parent.views;
    }

    const pageDefinition = {};

    const type = page.type;
    const typeList = type === 'PERSON' ? personTypePages : itemTypePages;
    const typeIndex = _.findIndex(typeList, { id: page.id });
    const indexes = {
        totalIndex: index,
        typeIndex,
    };
    const examples = isSubset ? subsetParentExamples : getDefaults(page, indexes);
    const views = isSubset ? subsetParentViews : getViews(page, examples);
    // ^ Later can have subset page with different default views

    pageDefinition.views = views;
    pageDefinition.defaultView = getDefaultView();

    if (!isSubset) {
        pageDefinition.examples = examples;
    }

    return pageDefinition;
}

function getFormattedPages(pagesArr, personTypePages, itemTypePages, otherPages) {
    return pagesArr.map((page, index) => {
        if (!page.pageType || !page.id) {
            warn(`Check that you are referencing the correct page in bundles.js.
                It looks like your use includes a page without any information`);
        }
        let pageDefinition = page;
        if (entityTypes.includes(page.pageType)) {
            const entityPageInfo = getEntityDefaultData(
                page,
                personTypePages,
                itemTypePages,
                index,
                otherPages);

            pageDefinition = {
                ...pageDefinition,
                ...entityPageInfo,
            };
        }
        return pageDefinition;
    });
}

function getDefaultData(pages) {
    const personTypePages = _.filter(pages, { type: 'PERSON' });
    const itemTypePages = _.filter(pages, { type: 'ITEM' });

    const subsetPages = pages.filter((page) => {
        return page.subset;
    });
    const subsetMainIds = _.map(subsetPages, 'subset.mainId');

    const nonSubsetPages = pages.filter((page) => {
        return !page.subset;
    });

    const nonSubsetPagesWithSpecifics = nonSubsetPages.map((page) => {
        const hasSubsets = subsetMainIds.includes(page.id);
        if (hasSubsets) {
            const specificDefaults = getPageSubsetInfo(subsetPages, page);
            return {
                ...page,
                specificDefaults,
            };
        }
        return page;
    });

    const formattedNonSubsetPages = getFormattedPages(nonSubsetPagesWithSpecifics, personTypePages, itemTypePages);
    let formattedPages = formattedNonSubsetPages;
    if (subsetPages?.length) {
        const formattedSubsetPages = getFormattedPages(
            subsetPages,
            personTypePages,
            itemTypePages,
            formattedNonSubsetPages);
        formattedPages = formattedPages.concat(formattedSubsetPages);
    }

    return formattedPages;
}

function getBundle(bundle) {
    const fullPages = pagesList[bundle];
    if (!fullPages?.length) {
        warn(`The bundle "${bundle}" returned no pages`);
    }
    return fullPages;
}

function getValidCustomizations(customizations) {
    return customizations.filter((customization) => {
        return _.has(customization, 'active') ? customization.active : true;
    });
}

function getMergedCustomizationsWithMap(customizations, mapping) {
    return customizations.map((customization) => {
        const customizationObj = _.find(mapping.customizations, { val: customization.customizationVal });
        const categoryObj = customizationObj.categories[customization.categoryKey];

        return {
            ...customization,
            mapping: categoryObj,
        };
    });
}

function getBundleFromOptions(setVal, options) {
    const optionObj = _.find(options, { optionVal: setVal });
    return getBundle(optionObj.bundle);
}

function dealWithCustomization(customization) {
    // Return arr of pages or one page obj;
    const mapping = customization.mapping;
    const actsOn = mapping.actsOn;
    const options = mapping.options;

    if (actsOn === 'bundle') {
        const selectedVal = customization.selected;
        if (_.isArray(selectedVal)) {
            return _(selectedVal).flatMap((val) => {
                return getBundleFromOptions(val, options);
            }).value();
        }
        return getBundleFromOptions(selectedVal, options);
    }
    return [];
}

function getModdedPages(pages, customizations) {
    const newPages = _.cloneDeep(pages);

    customizations.forEach((customization) => {
        const mapping = customization.mapping;
        const actsOn = mapping.actsOn;
        const options = mapping.options;
        const selected = customization.selected;
        const optionChanges = _.find(options, { optionVal: selected });
        const pagesActedOn = _.map(optionChanges.pageTypeChanges, 'page');

        if (actsOn === 'pageType') {
            newPages.forEach((page, index) => {
                if (pagesActedOn.includes(page.id) && page.pageType !== 'selected') {
                    const optionObj = _.find(optionChanges.pageTypeChanges, { page: page.id });
                    newPages[index].pageType = selected;
                    newPages[index].pageName = $t(`labels.${optionObj.pageNameKey}`);
                }
            });
        }
    });

    return newPages;
}

function getSpecificPages(pages, customizations) {
    // Unknown if there will ever be more than one customization of this type
    // for a use, but allowing it
    const newPages = [];

    // Here we take the pages so far unimpacted by these customizations
    // and set them, so they do not get replaced
    // Please note this is happening outside of the forEach, otherwise
    // subsequent customizations can re-add pages.

    const optionIds = _(customizations).flatMap((customization) => {
        return customization.mapping.options;
    }).map('pageVal').value();

    const permanentPages = pages.filter((page) => {
        return !optionIds.includes(page.id);
    });

    newPages.push(...permanentPages);

    customizations.forEach((customization) => {
        const selected = customization.selected;

        const customizationPageOptions = customization.mapping.options;
        const customizationOptionIds = _.map(customizationPageOptions, 'pageVal');

        const selectablePages = pages.filter((page) => {
            return customizationOptionIds.includes(page.id);
        });

        const isSelectedArr = _.isArray(selected);

        const selectedFormatted = isSelectedArr ? selected : [selected];

        const selectedPageVals = _(selectedFormatted).flatMap((item) => {
            const optionObj = _.find(customizationPageOptions, { optionVal: item });
            const pageValKey = optionObj.useKey ? 'useKey' : 'pageVal';
            return optionObj[pageValKey];
        }).value();

        if (selectablePages?.length) {
            selectablePages.forEach((page) => {
                const alreadyThere = _.find(newPages, { id: page.id });
                const pageCheckVal = page?.useKey || page?.id;
                const pageCheck = selectedPageVals.includes(pageCheckVal);
                if (!page) {
                    warn(`The page where selected is
                        "${selectedPageVals}" could not be found in function "getSpecificPages"`);
                } else if (!alreadyThere && pageCheck) {
                    newPages.push(page);
                }
                if (!alreadyThere && page.alwaysInclude) {
                    newPages.push(page);
                }
            });
        } else {
            selectedPageVals.forEach((pageVal) => {
                const newPage = allPages[pageVal];
                if (!newPage) {
                    warn(`The page you are looking for "${selectedPageVals}" could not be found`);
                } else {
                    newPages.push(newPage);
                }
            });
        }
    });
    return newPages;
}

function getCustomizedPages(customizations, bundle = null) {
    let pages = [];

    let selectedPages = [];

    if (bundle) {
        selectedPages = getBundle(bundle);
    }

    // Bundle in categories
    const moreBundles = _(customizations).flatMap((customization) => {
        return customization.mapping.fetchBundles;
    }).compact().value();

    if (moreBundles?.length) {
        moreBundles.forEach((additionalBundle) => {
            selectedPages = selectedPages.concat(getBundle(additionalBundle));
        });
    }
    pages = selectedPages;

    const actOnPageSelection = ['bundlePages'];
    const actOnExistingPages = ['pageType'];

    const initialCustomizations = customizations.filter((customization) => {
        return !actOnExistingPages.includes(customization.mapping.actsOn)
            && !actOnPageSelection.includes(customization.mapping.actsOn);
    });

    const selectionCustomizations = customizations.filter((customization) => {
        return actOnPageSelection.includes(customization.mapping.actsOn);
    });

    const applyLater = customizations.filter((customization) => {
        return actOnExistingPages.includes(customization.mapping.actsOn);
    });

    if (initialCustomizations?.length) {
        initialCustomizations.forEach((customization) => {
            const customizationPages = dealWithCustomization(customization);

            if (_.isArray(customizationPages)) {
                pages = pages.concat(customizationPages);
            } else {
                pages.push(customizationPages);
            }
            selectedPages = pages;
        });
    }
    if (selectionCustomizations?.length) {
        // Where the use either has a bundle and pages are picked off that bundle,
        // or individual pages are selected
        pages = getSpecificPages(selectedPages, selectionCustomizations);
        // Reset to only the specific ones used, necessary because we cannot
        // know which types of customizations will be applied
        selectedPages = pages;
    }

    if (applyLater?.length) {
        pages = getModdedPages(selectedPages, applyLater);
    }
    return pages;
}

function getPagesWithRefinements(use) {
    let pages = [];
    const refinement = use.refinement;
    if (!refinement.done) {
        return pages;
    }
    const mapping = use.refinementMap;
    const customizations = refinement.customizations;
    const customizationsWithMap = getMergedCustomizationsWithMap(customizations, mapping);
    const validCustomizations = getValidCustomizations(customizationsWithMap);

    pages = getCustomizedPages(validCustomizations, use.bundle);

    return pages;
}

// Merge the lists of pages that have the same mergeListsIds
function mergeListPages(pages) {
    const mergedPages = [];
    const nonMergablePages = [];
    pages.forEach((page) => {
        // If they don't have mergeListsIds we puth them in a separate array
        // and concatenate at the end.
        if (!_.has(page, 'mergeListsIds')) {
            nonMergablePages.push(page);
            return;
        }
        // Use intersection to see if any of the already merged pages share
        // mergeListsIds with the current page
        const alreadyThere = _.find(
            mergedPages,
            (mergedPage) => _.intersection(mergedPage.mergeListsIds, page.mergeListsIds).length
        );
        // If we find one we merge the lists and mergeListsIds (removing any duplicated)
        if (alreadyThere) {
            alreadyThere.lists = _.uniq(alreadyThere.lists.concat(page.lists));
            alreadyThere.mergeListsIds = _.uniq(alreadyThere.mergeListsIds.concat(page.mergeListsIds));
        } else {
            mergedPages.push(page);
        }
    });
    return mergedPages.concat(nonMergablePages);
}

export function getValidRelationships(page, pages) {
    const relationships = page.relationships;

    return _(relationships).map((relationship) => {
        const exists = pages.find((pageItem) => {
            return pageItem.id === relationship.to && !pageItem.subset;
        });

        if (exists) {
            return relationship;
        }

        const existed = pages.find((pageItem) => {
            return pageItem.oldIds?.includes(relationship.to);
        });

        if (existed) {
            const newToId = existed.id;

            return {
                ...relationship,
                to: newToId,
            };
        }
        return null;
    }).compact().value();
}

function adjustRelationships(pages) {
    const newPages = [];
    pages.forEach((page) => {
        const newPage = _.cloneDeep(page);
        if (page.relationships?.length) {
            newPage.relationships = getValidRelationships(page, pages);
        }
        newPages.push(newPage);
    });

    return newPages;
}

export function getFullPagesFromPagesArr(pages) {
    // Remove duplicates
    const uniqued = _.uniqBy(pages, 'id');
    // With defaults
    const pagesWithDefaults = getDefaultData(uniqued);
    // Merged lists
    const mergedPages = mergeListPages(pagesWithDefaults);

    return mergedPages;
}

function addressSubsetPages(pages) {
    // Address the fields of subset pages, adding
    // them to the parent where appropriate
    const subsetPages = pages.filter((page) => {
        return page.subset;
    });
    if (!subsetPages.length) {
        return pages;
    }
    const newPages = _.cloneDeep(pages);

    subsetPages.forEach((page) => {
        const parent = _.find(newPages, { id: page.subset.mainId });
        const parentFields = parent.fields;
        const subsetFields = page.fields;
        const allFields = parentFields;
        if (subsetFields) {
            allFields.push(...subsetFields);
        }
        const newFields = _.uniqBy(allFields, 'id');
        parent.fields = newFields;
    });
    return newPages;
}

function getCombinedPagesWithDefaults(spaceUses) {
    const pages = _(spaceUses).flatMap((use) => {
        const bundle = use.bundle;
        if (!use.refinementMap) {
            if (!bundle) {
                warn(`You are missing a bundle for "${use.val}"`);
            }
            return getBundle(bundle);
        }
        return getPagesWithRefinements(use);
    }).value();

    const pagesWithSubsetChanges = addressSubsetPages(pages);

    return getFullPagesFromPagesArr(pagesWithSubsetChanges);
    // return getFullPagesFromPagesArr(pages);
}

// function getCombinedMarkerGroups(spaceUses) {
//     const pages = _.flatMap(spaceUses, 'pages');
//     const pageMarkerGroups = _.flatMap(pages, 'markerGroups');

//     return _.uniqBy(pageMarkerGroups, 'group');
// }

function getCombinedContributors(space) {
    return space.uses.map((contributor) => {
        return {
            val: contributor.val,
        };
    });
}

function getValidUses(space) {
    const spaceUses = space.uses;
    const validUses = [];

    spaceUses.forEach((use) => {
        const fullUse = _.find(uses, { val: use.val });
        const pairIds = fullUse.ignoreIfAlsoSelected;

        const useObj = {
            ...fullUse,
            ...use,
        };

        if (pairIds?.length) {
            const arePairsSelected = spaceUses.some((useItem) => {
                return pairIds.includes(useItem.val);
            });
            if (!arePairsSelected) {
                validUses.push(useObj);
            }
        } else {
            validUses.push(useObj);
        }
    });
    return validUses;
}

function getPagesWithMods(pages, newPages) {
    const newPagesIds = newPages.map((page) => page.id);
    const newMainPages = _.filter(newPages, { newMainPage: true }) || [];
    const adjustedPages = newMainPages;

    pages.forEach((page) => {
        if (newPagesIds.includes(page.id)) {
            const newPage = _.find(newPages, { id: page.id });
            adjustedPages.push(newPage);
        } else {
            adjustedPages.push(page);
        }
    });

    const invalidSubsets = adjustedPages.filter((page) => {
        const mainId = page.subset?.mainId;
        const mainPage = !mainId ? null : _.find(adjustedPages, { id: mainId });
        return mainPage?.subset;
    });

    if (invalidSubsets?.length) {
        invalidSubsets.forEach((page) => {
            const oldMain = _.find(adjustedPages, { id: page.subset.mainId });
            const newMainId = oldMain.subset.mainId;
            const index = adjustedPages.findIndex((adjustedPage) => adjustedPage.id === page.id);
            adjustedPages[index].subset.mainId = newMainId;
        });
    }

    const pagesWithRelationshipAdjustment = adjustRelationships(adjustedPages);

    return pagesWithRelationshipAdjustment;
}

function getSpaces(spaces) {
    return spaces.map((space) => {
        const spaceUses = getValidUses(space);
        const pages = getCombinedPagesWithDefaults(spaceUses);
        const lists = getCombinedLists(pages);
        const spaceObj = {
            id: space.id,
            name: space.name,
            contributors: getCombinedContributors(space), // Used for the registration only
            pages,
            lists,
            // features: getCombinedFeatures(spaceUses), // Used for the registration only
            // markerGroups: getCombinedMarkerGroups(spaceUses), // Used for the registration only
        };

        if (space.newPages) {
            const newPages = getPagesWithMods(pages, space.newPages);
            spaceObj.oldPages = pages;
            spaceObj.pages = newPages;
        }
        return spaceObj;
    });
}

export function getMarkerGroupsFromPages(pages) {
    const markerGroups = _(pages).flatMap('markerGroups').compact().value();
    const unique = _.uniq(markerGroups);
    return unique.map((groupId) => {
        return getMarkerGroup(groupId);
    });
}

export function getCategoriesFromPages(pages) {
    const categories = _(pages).flatMap((page) => {
        return page.fields?.filter((field) => {
            return field.type === 'CATEGORY';
        });
    }).compact().value();

    if (!categories?.length) {
        return [];
    }

    // TODO: Check uniq below works
    const unique = _.uniqBy(categories, 'options.category');
    return unique.map((category) => {
        return getCategory(category.options.category);
    });
}

export function getFullStructure(spaces) {
    const allSpaces = getSpaces(spaces);
    const spacesArr = allSpaces.filter((space) => !!space.pages.length);
    const pages = _(spacesArr).flatMap('pages').value();

    const markerGroups = getMarkerGroupsFromPages(pages);
    const categories = getCategoriesFromPages(pages);

    return {
        markerGroups,
        categories,
        spaces: spacesArr,
    };
}

function flattenAndUnique(bluprints, dataKey) {
    const flat = _(bluprints).flatMap(dataKey).compact().value();
    if (flat?.length) {
        if (!_.isObject(flat[0])) {
            return _.uniq(flat);
        }
        const uniqKey = flat[0].id ? 'id' : 'val';
        return _.uniqBy(flat, uniqKey);
    }
    return [];
}

export function mergeBlueprints(pages, mergeVal) {
    // Input: Array of 2+ page objects with different blueprints
    // Output: Array of 3+ page objects (same number as above + 1) with the same blueprint
    // The pages are now subsets but otherwise unchanged, just the data structure is now shared
    const valFormatted = _.camelCase(mergeVal);
    const nameBase = $t(`registration.common.mergeTypes.${valFormatted}`);
    const name = `${nameBase} 1`;
    const singularName = `${$t(`registration.common.mergeTypes.singular.${valFormatted}`)} 1`;
    const mainId = `${mergeVal}1`;
    // TO REFACTOR: Name format once language matters
    const mainPageName = `All ${_.lowerCase(nameBase)}`;
    const fields = flattenAndUnique(pages, 'fields');
    const markerGroups = flattenAndUnique(pages, 'markerGroups');
    const relationships = flattenAndUnique(pages, 'relationships');
    const features = flattenAndUnique(pages, 'features');

    const mainPage = {
        ...pages[0],
        id: mainId,
        newMainPage: true,
        name,
        pageName: mainPageName,
        singularName,
        fields,
        markerGroups,
        relationships,
        features,
        oldIds: pages.map((page) => page.id),
        folder: '',
    };
    const newPages = [mainPage];

    pages.forEach((page) => {
        const pageName = page.pageName || page.name;
        const newPage = {
            id: page.id,
            pageName,
            symbol: page.symbol,
            hasBeenMerged: true,
            folder: page.folder || '',
            subset: getSubset(page.id, mainId),
        };

        newPages.push(newPage);
    });
    return newPages;
}

export default {
    getFullStructure,
};
