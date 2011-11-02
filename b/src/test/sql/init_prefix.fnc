create or replace function init_prefix return varchar2 is
  Result varchar2(10);
begin
  Result := 'nst_';
  return(Result);
end init_prefix;
/
