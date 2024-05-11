" This program is free software: you can redistribute it and/or modify
" it under the terms of the GNU General Public License version 2
" as published by the Free Software Foundation.
"
" This program is distributed in the hope that it will be useful,
" but WITHOUT ANY WARRANTY; without even the implied warranty of
" MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
" GNU General Public License for more details.
"
" You should have received a copy of the GNU General Public License
" along with this program. If not, see <http://www.gnu.org/licenses/>.
"
" Toggle Comment Line
" Usage: Press "q" to toggle comment
" Website: https://github.com/cakyus/vim-plugin

autocmd FileType php,js  let b:commetChars = '\/\/'
autocmd FileType sh      let b:commetChars = '#'
autocmd FileType vim     let b:commetChars = '"'
autocmd FileType sql     let b:commetChars = '--'

function! ToggleCommentLine()
  " replace commetChars at the beginning of line 
  " with commetChars plus a space
  execute ':silent! s/^\(.*\)/'.b:commetChars.' \1/g'
  " when empty line is commented trailing space will be removed
  execute ':silent! s/^'.b:commetChars.'$//g'
	" toggle comment line	
  execute ':silent! s/^'.b:commetChars.' '.b:commetChars.' //g'
endfunction

map <silent> q mZ:call ToggleCommentLine()<CR>`Z

