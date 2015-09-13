# read data from xml file
xml = open ('input.xml', 'rb').read ().decode ('utf-8');

# left and right tags
m_el = '<entry>'
m_er = '</entry>'
m_il = '<id>'
m_ir = '</id>'
m_tl = '<title>'
m_tr = '</title>'
m_al = '<author>'
m_ar = '</author>'
m_anl = '<name>'
m_anr = '</name>'

# extract string between `m_l` and `m_r` tags, starting from position `o`
# returns tuple (found string, end position of `m_r` tag)
def m_find (s, m_l, m_r, o = 0) :
	f = -1
	r = 0
	# find `m_l` position
	l = s.find (m_l, o)
	if l != -1 :
		l += len (m_l)
		# find `m_r` position
		r = s.find (m_r, l)
		f = s[l:r]
		r += len (m_r)
	return (f, r)

# escape single quotes in string for sql
def escape (s) :
	return s.replace ('\'', '\'\'')

part = xml
sql = ''
# sql statement template
m_sql = "INSERT INTO publications (title, author, link) VALUES ('%s', '%s', '%s');\r\n"
while True :
	# find <entry> tag
	(e, er) = m_find (part, m_el, m_er)
	if e != -1 :
		# find <id> tag
		(i, _) = m_find (e, m_il, m_ir)
		i = escape (i)
		i = i.replace ('\n', '')

		# find <title> tag
		(t, _) = m_find (e, m_tl, m_tr)
		t = t.replace ('\n', '')
		t = escape (t)
		print (t.encode('utf-8'))

		ax = []
		ao = 0
		# there can be more than one authors
		while True:
			# find <author> tag
			(a, ao) = m_find (e, m_al, m_ar, ao)
			if a != -1 :
				# find <name> tag
				(an, _) = m_find (a, m_anl, m_anr)
				ax.append (an)
			else :
				break
		# fill data into template, concatenate to `sql`
		sql += m_sql % (t, ','.join (ax), i)
		part = part[er:]
	else :
		break

# write data to sql file
open ('parsed.sql', 'wb').write (sql.encode ('utf-8'));
