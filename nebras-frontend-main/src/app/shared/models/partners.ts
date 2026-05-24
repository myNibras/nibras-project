export interface Partner {
  id: number;
  name: string;
  logo: string;
}

export interface PartnersSection {
  section_title: string;
  section_description: string;
  data: Partner[];
}

export interface PartnersResponse {
  status: boolean;
  message: string;
  data: PartnersSection;
}
