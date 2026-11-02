// CMS Lite — editor (source). Build with ./build.sh → ../editor.js
import { Editor, Node, mergeAttributes } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Image from '@tiptap/extension-image'
import { TableKit } from '@tiptap/extension-table'
import { Details, DetailsSummary, DetailsContent } from '@tiptap/extension-details'

// ---------------------------------------------------------------------------
// Labels: provided by the page (edit.php, <script id="i18n">), in the language
// chosen in the header bar. T('key', a, b…) replaces the %s in order.
// ---------------------------------------------------------------------------
const I18N = JSON.parse(document.getElementById('i18n')?.textContent || '{}')
function T(key, ...args) {
  let s = I18N[key] ?? key
  for (const a of args) s = s.replace('%s', a)
  return s
}

// ---------------------------------------------------------------------------
// Columns: produce exactly the Skeleton grid already present in style.css
//   <div class="row"><div class="one-half column">…</div>…</div>
// ---------------------------------------------------------------------------
const WIDTHS = ['one-half', 'one-third', 'two-thirds']
const LAYOUTS = {
  '1/2+1/2':     ['one-half', 'one-half'],
  '1/3+1/3+1/3': ['one-third', 'one-third', 'one-third'],
  '2/3+1/3':     ['two-thirds', 'one-third'],
  '1/3+2/3':     ['one-third', 'two-thirds'],
}
// Everything except "columns": no columns inside columns.
const COLUMN_CONTENT =
  '(paragraph|heading|bulletList|orderedList|blockquote|codeBlock|horizontalRule|image|table|audio|video|callout|details)+'

const Column = Node.create({
  name: 'column',
  content: COLUMN_CONTENT,
  isolating: true,
  defining: true,
  addAttributes() {
    return {
      width: {
        default: 'one-half',
        parseHTML: el => WIDTHS.find(w => el.classList.contains(w)) || 'one-half',
        renderHTML: () => ({}),
      },
    }
  },
  parseHTML() { return [{ tag: 'div.column' }] },
  renderHTML({ node }) { return ['div', { class: `${node.attrs.width} column` }, 0] },
})

function findColumns(state) {
  const { $from } = state.selection
  for (let d = $from.depth; d > 0; d--) {
    const node = $from.node(d)
    if (node.type.name === 'columns') return { node, pos: $from.before(d) }
  }
  return null
}

const Columns = Node.create({
  name: 'columns',
  group: 'block',
  content: 'column{2,3}',
  isolating: true,
  parseHTML() {
    return [{ tag: 'div.row', getAttrs: el => (el.querySelector(':scope > div.column') ? null : false) }]
  },
  renderHTML() { return ['div', { class: 'row' }, 0] },
  addCommands() {
    return {
      insertColumns: layout => ({ commands }) =>
        commands.insertContent({
          type: 'columns',
          content: LAYOUTS[layout].map(width => ({
            type: 'column', attrs: { width }, content: [{ type: 'paragraph' }],
          })),
        }),

      // Changes the layout; if the number of columns decreases,
      // the content of the removed columns joins the last remaining one.
      setColumnsLayout: layout => ({ state, tr, dispatch }) => {
        const found = findColumns(state)
        if (!found) return false
        const widths = LAYOUTS[layout]
        const schema = state.schema
        const oldCols = []
        found.node.forEach(c => oldCols.push(c))
        const cols = widths.map((width, i) => {
          let content
          if (i < oldCols.length) {
            content = oldCols[i].content
            if (i === widths.length - 1)
              for (let j = i + 1; j < oldCols.length; j++) content = content.append(oldCols[j].content)
          } else {
            content = schema.nodes.paragraph.create()
          }
          return schema.nodes.column.create({ width }, content)
        })
        if (dispatch) {
          tr.replaceWith(found.pos, found.pos + found.node.nodeSize, schema.nodes.columns.create(null, cols))
        }
        return true
      },

      // Removes the columns: their content is flattened, in order.
      unsetColumns: () => ({ state, tr, dispatch }) => {
        const found = findColumns(state)
        if (!found) return false
        const blocks = []
        found.node.forEach(col => col.forEach(b => blocks.push(b)))
        if (dispatch) tr.replaceWith(found.pos, found.pos + found.node.nodeSize, blocks)
        return true
      },
    }
  },
})

// ---------------------------------------------------------------------------
// Image: size (small / medium / full width) and position, through classes
// ---------------------------------------------------------------------------
const SIZES = ['ed-s', 'ed-m', 'ed-l']
// ed-left / ed-right: image on the left / right, the following text flows beside it (float)
// ed-block-left / ed-block-right: image alone on its line, aligned left / right
const ALIGNS = ['ed-left', 'ed-center', 'ed-right', 'ed-block-left', 'ed-block-right']

const SiteImage = Image.extend({
  addAttributes() {
    return {
      ...this.parent?.(),
      size: {
        default: 'ed-l',
        parseHTML: el => SIZES.find(c => el.classList.contains(c)) || 'ed-l',
        renderHTML: () => ({}),
      },
      align: {
        default: 'ed-center',
        parseHTML: el => ALIGNS.find(c => el.classList.contains(c)) || 'ed-center',
        renderHTML: () => ({}),
      },
    }
  },
  renderHTML({ node, HTMLAttributes }) {
    return ['img', mergeAttributes(HTMLAttributes, {
      class: `ed-img ${node.attrs.size} ${node.attrs.align}`,
    })]
  },
}).configure({ inline: false, allowBase64: false })

// ---------------------------------------------------------------------------
// Callouts (as in Docmost): <div class="ed-callout ed-callout-info">…</div>
// Five colours; no callout inside a callout, no columns inside a callout.
// ---------------------------------------------------------------------------
const CALLOUT_TYPES = ['info', 'note', 'success', 'warning', 'danger']
const Callout = Node.create({
  name: 'callout',
  group: 'block',
  content: '(paragraph|heading|bulletList|orderedList|blockquote|codeBlock|horizontalRule|image|table|audio|video)+',
  defining: true,
  addAttributes() {
    return {
      type: {
        default: 'info',
        parseHTML: el => CALLOUT_TYPES.find(t => el.classList.contains('ed-callout-' + t)) || 'info',
        renderHTML: () => ({}),
      },
    }
  },
  parseHTML() { return [{ tag: 'div.ed-callout' }] },
  renderHTML({ node }) { return ['div', { class: `ed-callout ed-callout-${node.attrs.type}` }, 0] },
})

// ---------------------------------------------------------------------------
// Audio player: <audio controls src="files/…"> (file sent as an attachment)
// ---------------------------------------------------------------------------
const AudioBlock = Node.create({
  name: 'audio',
  group: 'block',
  atom: true,
  draggable: true,
  selectable: true,
  addAttributes() { return { src: { default: null } } },
  parseHTML() { return [{ tag: 'audio[src]' }] },
  renderHTML({ HTMLAttributes }) {
    return ['audio', mergeAttributes(HTMLAttributes, { controls: '', preload: 'metadata', class: 'ed-audio' })]
  },
})

// 4.7.0: video player, on the same model: <video controls src="files/…">
// (mp4, webm sent as attachments; never autoplay)
const VideoBlock = Node.create({
  name: 'video',
  group: 'block',
  atom: true,
  draggable: true,
  selectable: true,
  addAttributes() { return { src: { default: null } } },
  parseHTML() { return [{ tag: 'video[src]' }] },
  renderHTML({ HTMLAttributes }) {
    return ['video', mergeAttributes(HTMLAttributes, { controls: '', preload: 'metadata', class: 'ed-video' })]
  },
})

// ---------------------------------------------------------------------------
// Upload
// ---------------------------------------------------------------------------
// Reads the API response. If it is not JSON (displayed PHP error, server error
// page…), the start of the received text is shown rather than a bare code.
async function readJson(r) {
  const text = await r.text()
  let j
  try { j = JSON.parse(text) } catch {
    const said = text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 160)
    throw new Error(T('unexpected_response', r.status) + (said ? ' : ' + said : ''))
  }
  if (!r.ok || j.error) throw new Error(j.error || T('server_error', r.status))
  return j
}

async function send(action, file) {
  const fd = new FormData()
  fd.append('file', file)
  const r = await fetch(`${ADMIN}api.php?action=${action}`, { method: 'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } })
  return readJson(r)
}

const isImage = f => /^image\/(jpeg|png|webp)$/.test(f.type)

// Images → image node; anything else → attachment, inserted as a link.
// The server decides on the actual type: this sorting is only a routing.
async function insertFiles(editor, files, pos) {
  for (const f of files) {
    status(T('sending', f.name))
    try {
      if (isImage(f)) {
        const { src } = await send('upload', f)
        const node = { type: 'image', attrs: { src, alt: '' } }
        if (pos != null) editor.chain().focus().insertContentAt(pos, node).run()
        else editor.chain().focus().insertContent(node).run()
        status(T('image_added'))
      } else {
        const { href, label, kind } = await send('attach', f)
        const link = { type: 'text', text: label, marks: [{ type: 'link', attrs: { href, target: '_blank' } }] }
        const chain = editor.chain().focus()
        if (pos != null) chain.setTextSelection(pos)
        // A link alone in a paragraph: that is how an appendix is presented.
        // For audio and video, the player comes above the link (which stays for downloading).
        const blocks = kind === 'audio' || kind === 'video'
          ? [{ type: kind, attrs: { src: href } }, { type: 'paragraph', content: [link] }]
          : [{ type: 'paragraph', content: [link] }]
        chain.insertContent(blocks).run()
        status(T('attach_added'))
      }
    } catch (e) { status(T('file_error', f.name, e.message), true) }
  }
}

// ---------------------------------------------------------------------------
// Interface
// ---------------------------------------------------------------------------
const $ = s => document.querySelector(s)
function status(msg, err = false) {
  const el = $('#status')
  el.textContent = msg
  el.classList.toggle('err', err)
}

const root = $('#editor')
// Editor URL: the page declares <base href> to the public site
const ADMIN = root.dataset.admin || ''
// Anti-CSRF token (4.5.0), sent back in the X-CSRF-Token header of every call
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || ''
const initial = JSON.parse($('#initial-content').textContent)
let dirty = false

const editor = new Editor({
  element: root,
  extensions: [
    StarterKit.configure({
      heading: { levels: [2, 3, 4] },
      link: { openOnClick: false, autolink: true, defaultProtocol: 'https' },
    }),
    Columns,
    Column,
    SiteImage,
    AudioBlock,
    VideoBlock,
    Callout,
    // Collapsible blocks: <details><summary>…</summary><div data-type="detailsContent">…</div></details>
    // (native HTML: on the site, the browser opens and closes them without JavaScript)
    Details.configure({ persist: true, HTMLAttributes: { class: 'ed-details' } }),
    DetailsSummary,
    DetailsContent,
    // Simple tables: no resizing with the mouse, no merging of cells
    // (unmanageable on a phone). Tab moves to the next cell, and adds a
    // row at the end of the table.
    TableKit.configure({ table: { resizable: false, HTMLAttributes: { class: 'ed-table' } } }),
  ],
  content: initial,
  editorProps: {
    handleDrop(view, event, _slice, moved) {
      const files = [...(event.dataTransfer?.files || [])]
      if (moved || !files.length) return false
      event.preventDefault()
      const pos = view.posAtCoords({ left: event.clientX, top: event.clientY })?.pos
      insertFiles(editor, files, pos)
      return true
    },
    handlePaste(_view, event) {
      const files = [...(event.clipboardData?.files || [])]
      if (!files.length) return false
      event.preventDefault()
      insertFiles(editor, files, null)
      return true
    },
  },
  onUpdate() { dirty = true; status(T('unsaved')) },
  onTransaction() { refreshToolbar() },
})

// ---------------------------------------------------------------------------
// Link dialog: free address, or a page of the site chosen by its title.
// Addresses are completed: e-mail -> mailto:, www.… -> https://, phone -> tel:
// ---------------------------------------------------------------------------
const PAGES = JSON.parse(document.getElementById('site-pages')?.textContent || '[]')

function normalizeHref(raw) {
  const v = raw.trim()
  if (v === '') return ''
  if (/^(https?:|mailto:|tel:|#|\/)/i.test(v) || /^page\.php\?id=\d+$/.test(v) || /^(files|images)\//.test(v)) return v
  if (/^[^\s@\/]+@[^\s@\/]+\.[a-z]{2,}$/i.test(v)) return 'mailto:' + v
  if (/^\+?[0-9][0-9 .\/-]{5,}$/.test(v)) return 'tel:' + v.replace(/[ .\/-]/g, '')
  if (/^www\./i.test(v) || /^[a-z0-9-]+(\.[a-z0-9-]+)+(\/.*)?$/i.test(v)) return 'https://' + v
  return v
}

// Link text when nothing is selected: the title of the chosen page,
// otherwise the address as typed (with its spaces, without "https://")
function linkLabel(href, typed) {
  const m = /^page\.php\?id=(\d+)$/.exec(href)
  if (m) return PAGES.find(p => String(p.id) === m[1])?.title || href
  return typed.trim().replace(/^(mailto:|tel:|https?:\/\/)/i, '')
}

function openLinkDialog() {
  const dlg = $('#link-dialog'), url = $('#link-url'), page = $('#link-page')
  const current = editor.getAttributes('link').href || ''
  url.value = current
  page.value = PAGES.some(p => 'page.php?id=' + p.id === current) ? current : ''
  $('#link-remove').hidden = !editor.isActive('link')
  dlg.showModal()
  url.focus()
  url.select()
}

function applyLink() {
  const typed = $('#link-url').value
  const href = normalizeHref(typed)
  $('#link-dialog').close()
  const chain = editor.chain().focus()
  if (href === '') { chain.extendMarkRange('link').unsetLink().run(); return }
  if (editor.state.selection.empty && !editor.isActive('link')) {
    // Nothing selected: a readable text is inserted, already linked
    chain.insertContent({ type: 'text', text: linkLabel(href, typed), marks: [{ type: 'link', attrs: { href } }] })
         .unsetMark('link').run()
  } else {
    chain.extendMarkRange('link').setLink({ href }).run()
  }
}

document.getElementById('link-page')?.addEventListener('change', e => {
  if (e.target.value) $('#link-url').value = e.target.value
})
document.getElementById('link-url')?.addEventListener('input', () => { $('#link-page').value = '' })
document.getElementById('link-form')?.addEventListener('submit', e => { e.preventDefault(); applyLink() })
document.getElementById('link-remove')?.addEventListener('click', () => {
  $('#link-dialog').close()
  editor.chain().focus().extendMarkRange('link').unsetLink().run()
})
document.getElementById('link-cancel')?.addEventListener('click', () => { $('#link-dialog').close(); editor.commands.focus() })

// Buttons: data-cmd="…" → action
const actions = {
  p:      () => editor.chain().focus().setParagraph().run(),
  h2:     () => editor.chain().focus().toggleHeading({ level: 2 }).run(),
  h3:     () => editor.chain().focus().toggleHeading({ level: 3 }).run(),
  h4:     () => editor.chain().focus().toggleHeading({ level: 4 }).run(),
  bold:   () => editor.chain().focus().toggleBold().run(),
  italic: () => editor.chain().focus().toggleItalic().run(),
  underline: () => editor.chain().focus().toggleUnderline().run(),
  ul:     () => editor.chain().focus().toggleBulletList().run(),
  ol:     () => editor.chain().focus().toggleOrderedList().run(),
  quote:  () => editor.chain().focus().toggleBlockquote().run(),
  code:      () => editor.chain().focus().toggleCode().run(),
  codeblock: () => editor.chain().focus().toggleCodeBlock().run(),
  hr:     () => editor.chain().focus().setHorizontalRule().run(),
  undo:   () => editor.chain().focus().undo().run(),
  redo:   () => editor.chain().focus().redo().run(),
  link: () => openLinkDialog(),
  image: () => $('#file').click(),
  table: () => editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run(),
  tblRowAdd: () => editor.chain().focus().addRowAfter().run(),
  tblColAdd: () => editor.chain().focus().addColumnAfter().run(),
  tblRowDel: () => editor.chain().focus().deleteRow().run(),
  tblColDel: () => editor.chain().focus().deleteColumn().run(),
  tblHeader: () => editor.chain().focus().toggleHeaderRow().run(),
  callout: () => editor.chain().focus().wrapIn('callout', { type: 'info' }).run(),
  calloutRemove: () => editor.chain().focus().lift('callout').run(),
  // A single paragraph: it becomes the TITLE of the block (a FAQ question, for example),
  // the block opens and the cursor waits for the answer. Several paragraphs selected:
  // they become the content of the block, to be titled afterwards.
  details: () => {
    const { $from, $to } = editor.state.selection
    if ($from.sameParent($to) && $from.parent.type.name === 'paragraph') {
      const title = $from.parent.textContent
      const from = $from.before(), to = $from.after()
      editor.chain().focus().insertContentAt({ from, to }, {
        type: 'details', attrs: { open: true }, content: [
          { type: 'detailsSummary', content: title ? [{ type: 'text', text: title }] : [] },
          { type: 'detailsContent', content: [{ type: 'paragraph' }] },
        ],
      }).setTextSelection(from + title.length + 5).run()
    } else {
      editor.chain().focus().setDetails().updateAttributes('details', { open: true }).run()
    }
  },
  detailsRemove: () => editor.chain().focus().unsetDetails().run(),
  tblDelete: () => {
    if (confirm(T('tbl_delete_confirm'))) editor.chain().focus().deleteTable().run()
  },
  attach: () => $('#attach-file').click(),
  unsetColumns: () => editor.chain().focus().unsetColumns().run(),
}
for (const t of CALLOUT_TYPES) actions['callout-' + t] = () => editor.chain().focus().updateAttributes('callout', { type: t }).run()
for (const s of SIZES) actions[s] = () => editor.chain().focus().updateAttributes('image', { size: s }).run()
for (const a of ALIGNS) actions[a] = () => editor.chain().focus().updateAttributes('image', { align: a }).run()

document.querySelectorAll('[data-cmd]').forEach(b =>
  b.addEventListener('click', e => { e.preventDefault(); actions[b.dataset.cmd]() }))

$('#columns').addEventListener('change', e => {
  const layout = e.target.value
  e.target.value = ''
  if (!layout) return
  const chain = editor.chain().focus()
  if (editor.isActive('columns')) chain.setColumnsLayout(layout).run()
  else chain.insertColumns(layout).run()
})

for (const id of ['#file', '#attach-file']) {
  $(id).addEventListener('change', e => {
    insertFiles(editor, [...e.target.files], null)
    e.target.value = ''
  })
}

function refreshToolbar() {
  const on = (cmd, active) => document.querySelector(`[data-cmd="${cmd}"]`)?.classList.toggle('on', active)
  on('bold', editor.isActive('bold'))
  on('italic', editor.isActive('italic'))
  on('underline', editor.isActive('underline'))
  on('h2', editor.isActive('heading', { level: 2 }))
  on('h3', editor.isActive('heading', { level: 3 }))
  on('h4', editor.isActive('heading', { level: 4 }))
  on('ul', editor.isActive('bulletList'))
  on('ol', editor.isActive('orderedList'))
  on('quote', editor.isActive('blockquote'))
  on('code', editor.isActive('code'))
  on('codeblock', editor.isActive('codeBlock'))
  on('link', editor.isActive('link'))

  const img = editor.isActive('image')
  $('#image-tools').hidden = !img
  if (img) {
    const { size, align } = editor.getAttributes('image')
    for (const s of SIZES) on(s, s === size)
    for (const a of ALIGNS) on(a, a === align)
  }
  $('#table-tools').hidden = !editor.isActive('table')
  const inCallout = editor.isActive('callout')
  $('#callout-tools').hidden = !inCallout
  if (inCallout) { const t = editor.getAttributes('callout').type; for (const x of CALLOUT_TYPES) on('callout-' + x, x === t) }
  $('#details-tools').hidden = !editor.isActive('details')

  const inCols = editor.isActive('columns')
  document.querySelector('[data-cmd="unsetColumns"]').hidden = !inCols
  $('#columns option[value=""]').textContent = inCols ? T('col_change') : T('col_insert')
}
refreshToolbar()

// ---------------------------------------------------------------------------
// Saving
// ---------------------------------------------------------------------------
async function save() {
  const title = $('#title').value.trim()
  if (!title) { status(T('title_required'), true); $('#title').focus(); return }
  status(T('saving'))
  const body = {
    id: Number($('#page-id').value) || 0,
    title,
    draft: $('#draft').checked,
    in_menu: $('#in-menu').checked,
    html: editor.getHTML(),
    // 4.5.0: page properties (category, order, author, date)
    categ: $('#prop-categ').value,
    categ_pagenum: $('#prop-pagenum').value,
    author: $('#prop-author').value,
    date: $('#prop-date').value,
  }
  try {
    const r = await fetch(`${ADMIN}api.php?action=save`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify(body),
    })
    const j = await readJson(r)
    if (!body.id) {
      $('#page-id').value = j.id
      history.replaceState(null, '', `${ADMIN}edit.php?id=${j.id}`)
    }
    $('#view-link').href = j.url
    $('#view-link').hidden = false
    dirty = false
    status(body.draft ? T('saved_draft') : T('saved_published'))
  } catch (e) { status(e.message, true) }
}

$('#save').addEventListener('click', e => { e.preventDefault(); save() })
$('#title').addEventListener('input', () => { dirty = true })
$('#draft').addEventListener('change', () => { dirty = true })
$('#in-menu').addEventListener('change', () => { dirty = true })
for (const s of ['#prop-categ', '#prop-pagenum', '#prop-author', '#prop-date']) {
  $(s).addEventListener('input', () => { dirty = true })
}
document.addEventListener('keydown', e => {
  if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); save() }
})
window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = '' } })
