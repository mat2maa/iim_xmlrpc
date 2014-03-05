reload!

require 'xmlrpc/client' 

class XMLRPC::Client
 def disableSSLVerification
   @http.verify_mode = OpenSSL::SSL::VERIFY_NONE
   warn "Proxyman SSL Verification disabled"
 end
end

app_id = "83u948$%@^po93kdERVsdfWRG932$%@jr3248jr2(K^!D39480^ry32HTEW984"

nas_url = "https://imagesinmotion.no-ip.biz:8081/server/audio_file_operations.php"

client = XMLRPC::Client.new_from_uri(nas_url)

client.disableSSLVerification()
client.http_header_extra = { 'Content-Type' => 'text/xml' }
client.timeout = nil

@playlist_id = 3979.to_s
playlist = AudioPlaylist.find(@playlist_id)

@tracks_found = playlist.audio_playlist_tracks_sorted.delete_if { |playlist_track| playlist_track.track.mp3_exists==false }

@albums = @tracks_found.map { |t| t.track.album_id }.flatten
@tracks = @tracks_found.map { |t| t.track.track_num }.flatten
@track_positions = @tracks_found.map { |t| t.position.to_s }.flatten

begin
  result = client.call2('create_tracks_zip', app_id, @playlist_id, @albums, @tracks, @track_positions)
rescue Timeout::Error => e
  puts 'Could not connect to NAS'
end


reload!

require 'xmlrpc/client' 

class XMLRPC::Client
 def disableSSLVerification
   @http.verify_mode = OpenSSL::SSL::VERIFY_NONE
   warn "Proxyman SSL Verification disabled"
 end
end

app_id = "83u948$%@^po93kdERVsdfWRG932$%@jr3248jr2(K^!D39480^ry32HTEW984"

nas_url = "https://imagesinmotion.no-ip.biz:8081/server/audio_file_operations.php"

client = XMLRPC::Client.new_from_uri(nas_url)

client.disableSSLVerification()
client.http_header_extra = { 'Content-Type' => 'text/xml' }
client.timeout = nil

@playlist_id = 536.to_s
playlist = AlbumPlaylist.find(@playlist_id)

@albums_found = playlist.album_playlist_items_sorted.delete_if{|playlist_album| playlist_album.album.mp3_exists==false}

@album_positions = @albums_found.map{|t| t.position.to_s }.flatten
@albums = @albums_found.map{|t| t.album_id }.flatten
@tracks = @albums_found.map{|t| t.album.tracks_count }.flatten

begin
  result = client.call2('create_albums_zip', app_id, @playlist_id, @albums, @tracks, @album_positions)
rescue Timeout::Error => e
  puts 'Could not connect to NAS'
end


require 'xmlrpc/client'
client = XMLRPC::Client.new_from_uri('https://testpypi.python.org/pypi')
client.http_header_extra = { 'Content-Type' => 'text/xml' }
result = client.call(:list_packages)
puts result.inspect