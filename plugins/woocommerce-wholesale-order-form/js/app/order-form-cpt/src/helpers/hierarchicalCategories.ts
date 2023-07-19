export interface ICategory {
  term_id: number,
  name: string,
  slug: string,
  taxonomy: string,
  description: string,
  term_group: number,
  term_taxonomy_id: number,
  parent: number,
  count: number,
  filter: string,
}

export interface IHierarchyObject {
  title: string,
  value: string,
  children: IHierarchyObject[],
}

/**
 * Build hierarchical categories list
 *
 * @param {ICategory[]} categories Categories list returned by WordPress API
 * @param {number} parent The category parent ID
 */
const getHierarchicalCategories = (categories: ICategory[], parent = 0): IHierarchyObject[] => {

  let hierarchy: IHierarchyObject[] = []

  const terms = categories.filter(term => parent === term.parent)

  let htmlDecode = (input: string): string | null => {
    const doc = new DOMParser().parseFromString(input, 'text/html')

    return doc.documentElement.textContent
  }

  terms.forEach(term => {
    const children = getHierarchicalCategories(categories, term.term_id)

    let parentTerm = categories.filter(cat => cat.term_id === term.parent)[0] ?? null
    while (parentTerm && 0 !== parentTerm.parent) {
      parentTerm = categories.filter(cat => cat.term_id === parentTerm.parent)[0] ?? null
    }
    hierarchy.push({ title: htmlDecode(term.name) ?? `${term.term_id}`, value: term.slug, children })
  })

  return hierarchy
}

export default getHierarchicalCategories
